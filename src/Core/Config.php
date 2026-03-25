<?php

namespace FlowUI\Core;

class Config
{
    private array $data = [
        'debug' => false,
        'script_url' => '/assets/js/flow-ui.js',
        'csrf_enabled' => true,
        'csrf_token_name' => '_token',
        'session_key_prefix' => '_flow_',
    ];

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function merge(array $config): void
    {
        $this->data = array_merge($this->data, $config);
    }

    public function all(): array
    {
        return $this->data;
    }
}
