<?php

namespace FlowUI\Core;

use FlowUI\Forms\FormProcessor;
use FlowUI\Validation\Validator;
use FlowUI\Processors\DropdownProcessor;
use FlowUI\Processors\TableProcessor;
use FlowUI\Processors\AlertProcessor;
use Masterminds\HTML5;

class FlowUI
{
    private static ?self $instance = null;
    private HTML5 $parser;
    private Config $config;
    private Session $session;
    private Cache $cache;
    private array $processors = [];
    private bool $isCapturing = false;

    private function __construct()
    {
        $this->parser = new HTML5(['disable_html_ns' => true]);
        $this->config = new Config();
        $this->session = new Session();
        $this->cache = new Cache(
            $this->config->get('cache_dir'),
            $this->config->get('cache_enabled', true)
        );
        $this->registerDefaultProcessors();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerDefaultProcessors(): void
    {
        $this->processors[] = new FormProcessor($this->session, $this->config);
        $this->processors[] = new AlertProcessor($this->session, $this->config);
        $this->processors[] = new DropdownProcessor($this->config);
        $this->processors[] = new TableProcessor($this->config);
    }

    public static function start(array $config = []): string
    {
        Performance::start('flowui_total');
        Performance::mark('flowui_start');
        
        $instance = self::getInstance();
        if (!empty($config)) {
            $instance->config->merge($config);
        }
        $instance->isCapturing = true;
        ob_start();
        return '';
    }

    public static function end(): string
    {
        $instance = self::getInstance();
        $html = ob_get_clean();
        $instance->isCapturing = false;
        
        if (empty($html)) {
            return '';
        }

        Performance::mark('before_process');
        $result = $instance->process($html);
        Performance::mark('after_process');
        
        $elapsed = Performance::end('flowui_total');
        
        if ($instance->config->get('debug', false) && $instance->config->get('show_performance', false)) {
            $result .= $instance->renderPerformanceStats($elapsed);
        }

        return $result;
    }

    private function process(string $html): string
    {
        try {
            // Generate cache key from HTML content
            $cacheKey = $this->cache->generateKey($html);
            $useCache = $this->config->get('cache_enabled', true) && 
                        !$this->hasDynamicContent();
            
            // Try to get from cache
            if ($useCache && $this->cache->has($cacheKey)) {
                $cached = $this->cache->get($cacheKey);
                if ($cached !== null) {
                    return $this->injectClientScript($cached);
                }
            }
            
            // Process HTML
            $dom = $this->parser->loadHTML($html);
            
            foreach ($this->processors as $processor) {
                $processor->process($dom);
            }

            $processedHtml = $this->parser->saveHTML($dom);
            
            // Cache the result (without script tag)
            if ($useCache) {
                $this->cache->set($cacheKey, $processedHtml, 
                    $this->config->get('cache_ttl', 3600));
            }
            
            return $this->injectClientScript($processedHtml);
        } catch (\Exception $e) {
            if ($this->config->get('debug', false)) {
                return $this->renderError($e, $html);
            }
            return $html;
        }
    }

    private function hasDynamicContent(): bool
    {
        // Don't cache if there are errors or old input (form state)
        return $this->session->has('_errors') || 
               $this->session->has('_old') ||
               $this->session->has('_alert_success') ||
               $this->session->has('_alert_error') ||
               $this->session->has('_alert_warning') ||
               $this->session->has('_alert_info');
    }

    private function injectClientScript(string $html): string
    {
        $scriptTag = '<script src="' . $this->config->get('script_url', '/assets/js/flow-ui.js') . '"></script>';
        
        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $scriptTag . "\n</body>", $html);
        }
        
        return $html . "\n" . $scriptTag;
    }

    private function renderError(\Exception $e, string $originalHtml): string
    {
        $error = htmlspecialchars($e->getMessage());
        $trace = htmlspecialchars($e->getTraceAsString());
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>FlowUI Error</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .error-box { background: #fff; border-left: 4px solid #d32f2f; padding: 20px; margin: 20px 0; }
        .error-title { color: #d32f2f; font-size: 20px; margin-bottom: 10px; }
        .error-trace { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-title">FlowUI Processing Error</div>
        <div><strong>Message:</strong> {$error}</div>
        <div class="error-trace"><strong>Trace:</strong><br>{$trace}</div>
    </div>
    <div class="error-box">
        <div class="error-title">Original HTML</div>
        <pre>{$originalHtml}</pre>
    </div>
</body>
</html>
HTML;
    }

    public static function validateRequest(): bool
    {
        $instance = self::getInstance();
        $validator = new Validator($instance->config);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        if (!$validator->validateCsrf($instance->session)) {
            return false;
        }

        $rules = $instance->session->get('_flow_validation_rules', []);
        if (empty($rules)) {
            return true;
        }

        $errors = $validator->validate($_POST, $rules);
        
        if (!empty($errors)) {
            $instance->session->flash('_errors', $errors);
            $instance->session->flash('_old', $_POST);
            return false;
        }

        return true;
    }

    public static function config(string $key, $default = null)
    {
        return self::getInstance()->config->get($key, $default);
    }

    public static function alert(string $type, string $message): void
    {
        $instance = self::getInstance();
        $key = "_alert_{$type}";
        $current = $instance->session->get($key, []);
        
        if (is_string($current)) {
            $current = [$current];
        }
        
        $current[] = $message;
        $instance->session->flash($key, $current);
    }

    public static function success(string $message): void
    {
        self::alert('success', $message);
    }

    public static function error(string $message): void
    {
        self::alert('error', $message);
    }

    public static function warning(string $message): void
    {
        self::alert('warning', $message);
    }

    public static function info(string $message): void
    {
        self::alert('info', $message);
    }

    public static function cache(): Cache
    {
        return self::getInstance()->cache;
    }

    public static function clearCache(): bool
    {
        return self::getInstance()->cache->clear();
    }

    private function renderPerformanceStats(?float $elapsed): string
    {
        $report = Performance::getReport();
        $cacheStats = $this->cache->getStats();
        
        $stats = sprintf(
            '<!-- FlowUI Performance Stats
Total Time: %s
Memory Peak: %s
Cache: %s (%d files)
Marks: %d
Counters: %s
-->',
            Performance::formatTime($elapsed ?? 0),
            Performance::formatBytes($report['memory']['peak']),
            $cacheStats['enabled'] ? 'Enabled' : 'Disabled',
            $cacheStats['total_files'],
            count($report['marks']),
            json_encode($report['counters'])
        );
        
        return "\n" . $stats;
    }
}
