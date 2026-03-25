<?php

namespace FlowUI\Core;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, $value): void
    {
        $this->set($key, $value);
        $this->set('_flash_keys', array_merge(
            $this->get('_flash_keys', []),
            [$key]
        ));
    }

    public function getFlash(string $key, $default = null)
    {
        $value = $this->get($key, $default);
        return $value;
    }

    public function clearFlash(): void
    {
        $flashKeys = $this->get('_flash_keys', []);
        foreach ($flashKeys as $key) {
            $this->remove($key);
        }
        $this->remove('_flash_keys');
    }

    public function regenerateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->set('_csrf_token', $token);
        return $token;
    }

    public function getToken(): string
    {
        if (!$this->has('_csrf_token')) {
            return $this->regenerateToken();
        }
        return $this->get('_csrf_token');
    }
}
