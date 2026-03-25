<?php

namespace FlowUI\Core;

class Cache
{
    private string $cacheDir;
    private bool $enabled;

    public function __construct(?string $cacheDir = null, bool $enabled = true)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/flowui_cache';
        $this->enabled = $enabled;
        
        if ($this->enabled && !is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }

        $data = @file_get_contents($file);
        
        if ($data === false) {
            return null;
        }

        $decoded = json_decode($data, true);

        if (!is_array($decoded)) {
            return null;
        }

        // Check expiration
        if (isset($decoded['expires']) && $decoded['expires'] < time()) {
            $this->delete($key);
            return null;
        }

        return isset($decoded['value']) ? (string)$decoded['value'] : null;
    }

    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($key);
        $data = json_encode([
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ]);

        return @file_put_contents($file, $data, LOCK_EX) !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return @unlink($file);
        }

        return true;
    }

    public function clear(): bool
    {
        if (!$this->enabled || !is_dir($this->cacheDir)) {
            return false;
        }

        $files = glob($this->cacheDir . '/*');
        
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = @unlink($file) && $success;
            }
        }

        return $success;
    }

    public function generateKey(string $content): string
    {
        return hash('sha256', $content);
    }

    private function getCacheFile(string $key): string
    {
        return $this->cacheDir . '/' . $key . '.cache';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function getStats(): array
    {
        if (!$this->enabled || !is_dir($this->cacheDir)) {
            return [
                'enabled' => false,
                'total_files' => 0,
                'total_size' => 0,
            ];
        }

        $files = glob($this->cacheDir . '/*');
        $totalSize = 0;

        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }
        }

        return [
            'enabled' => true,
            'total_files' => count($files ?: []),
            'total_size' => $totalSize,
            'cache_dir' => $this->cacheDir,
        ];
    }
}
