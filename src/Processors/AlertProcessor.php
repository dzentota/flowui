<?php

namespace FlowUI\Processors;

use FlowUI\Core\Session;
use FlowUI\Core\Config;
use DOMDocument;
use DOMElement;
use DOMXPath;

class AlertProcessor implements ProcessorInterface
{
    private Session $session;
    private Config $config;

    public function __construct(Session $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    public function process(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        
        // Find alert container or create one
        $container = $xpath->query('//*[@data-alerts or @id="flow-alerts"]')->item(0);
        
        if (!$container) {
            // Check if we should auto-inject alerts
            $body = $xpath->query('//body')->item(0);
            if ($body && $this->config->get('auto_inject_alerts', true)) {
                $container = $dom->createElement('div');
                $container->setAttribute('id', 'flow-alerts');
                $container->setAttribute('class', 'flow-alerts-container');
                
                // Insert at beginning of body
                if ($body->firstChild) {
                    $body->insertBefore($container, $body->firstChild);
                } else {
                    $body->appendChild($container);
                }
            }
        }
        
        if ($container) {
            $this->injectAlerts($container, $dom);
        }
        
        // Process static alerts with auto-dismiss
        $alerts = $xpath->query('//*[@data-alert]');
        foreach ($alerts as $alert) {
            $this->processStaticAlert($alert);
        }
    }

    private function injectAlerts(DOMElement $container, DOMDocument $dom): void
    {
        // Get flash messages
        $messages = [
            'success' => $this->session->getFlash('_alert_success', []),
            'error' => $this->session->getFlash('_alert_error', []),
            'warning' => $this->session->getFlash('_alert_warning', []),
            'info' => $this->session->getFlash('_alert_info', []),
        ];
        
        foreach ($messages as $type => $msgs) {
            if (is_string($msgs)) {
                $msgs = [$msgs];
            }
            
            foreach ($msgs as $message) {
                if (empty($message)) continue;
                
                $alert = $this->createAlert($dom, $type, $message);
                $container->appendChild($alert);
            }
        }
    }

    private function createAlert(DOMDocument $dom, string $type, string $message): DOMElement
    {
        $alert = $dom->createElement('div');
        $alert->setAttribute('class', "flow-alert flow-alert-{$type}");
        $alert->setAttribute('role', 'alert');
        $alert->setAttribute('data-alert', $type);
        
        // Icon span
        $icon = $dom->createElement('span');
        $icon->setAttribute('class', 'flow-alert-icon');
        $icon->nodeValue = $this->getIcon($type);
        $alert->appendChild($icon);
        
        // Message span
        $messageSpan = $dom->createElement('span');
        $messageSpan->setAttribute('class', 'flow-alert-message');
        $messageSpan->nodeValue = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $alert->appendChild($messageSpan);
        
        // Close button
        $closeBtn = $dom->createElement('button');
        $closeBtn->setAttribute('class', 'flow-alert-close');
        $closeBtn->setAttribute('aria-label', 'Close');
        $closeBtn->nodeValue = '×';
        $alert->appendChild($closeBtn);
        
        return $alert;
    }

    private function processStaticAlert(DOMElement $alert): void
    {
        $type = $alert->getAttribute('data-alert');
        $alert->setAttribute('class', trim($alert->getAttribute('class') . " flow-alert flow-alert-{$type}"));
        $alert->setAttribute('role', 'alert');
        
        // Add close button if auto-dismiss is enabled
        if ($alert->hasAttribute('data-dismissible') || 
            $alert->getAttribute('data-dismissible') !== 'false') {
            
            // Check if close button already exists
            $hasCloseBtn = false;
            foreach ($alert->childNodes as $child) {
                if ($child instanceof DOMElement && 
                    $child->hasAttribute('class') && 
                    strpos($child->getAttribute('class'), 'flow-alert-close') !== false) {
                    $hasCloseBtn = true;
                    break;
                }
            }
            
            if (!$hasCloseBtn) {
                $doc = $alert->ownerDocument;
                $closeBtn = $doc->createElement('button');
                $closeBtn->setAttribute('class', 'flow-alert-close');
                $closeBtn->setAttribute('aria-label', 'Close');
                $closeBtn->nodeValue = '×';
                $alert->appendChild($closeBtn);
            }
        }
    }

    private function getIcon(string $type): string
    {
        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
        ];
        
        return $icons[$type] ?? 'ℹ';
    }
}
