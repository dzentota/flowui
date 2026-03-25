<?php

namespace FlowUI\Validation;

use FlowUI\Core\Session;
use FlowUI\Core\Config;

class Validator
{
    private Config $config;
    private array $rules = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->registerDefaultRules();
    }

    private function registerDefaultRules(): void
    {
        $this->rules['required'] = function ($value, $params) {
            return !empty($value) || $value === '0';
        };

        $this->rules['email'] = function ($value, $params) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        };

        $this->rules['min'] = function ($value, $params) {
            $min = (int)($params[0] ?? 0);
            return strlen($value) >= $min;
        };

        $this->rules['max'] = function ($value, $params) {
            $max = (int)($params[0] ?? PHP_INT_MAX);
            return strlen($value) <= $max;
        };

        $this->rules['numeric'] = function ($value, $params) {
            return is_numeric($value);
        };

        $this->rules['alpha'] = function ($value, $params) {
            return ctype_alpha($value);
        };

        $this->rules['alphanumeric'] = function ($value, $params) {
            return ctype_alnum($value);
        };

        $this->rules['url'] = function ($value, $params) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        };

        $this->rules['confirmed'] = function ($value, $params, $field, $data) {
            $confirmField = $field . '_confirmation';
            return isset($data[$confirmField]) && $value === $data[$confirmField];
        };
    }

    public function validate(array $data, array $rulesMap): array
    {
        $errors = [];

        foreach ($rulesMap as $field => $rulesString) {
            $rules = explode('|', $rulesString);
            $value = $data[$field] ?? '';

            foreach ($rules as $rule) {
                $ruleName = $rule;
                $params = [];

                if (strpos($rule, ':') !== false) {
                    [$ruleName, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                if (!isset($this->rules[$ruleName])) {
                    continue;
                }

                $validator = $this->rules[$ruleName];
                $isValid = $validator($value, $params, $field, $data);

                if (!$isValid) {
                    $errors[$field][] = $this->getMessage($ruleName, $field, $params);
                }
            }
        }

        return $errors;
    }

    private function getMessage(string $rule, string $field, array $params): string
    {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        
        $messages = [
            'required' => "The {$fieldName} field is required.",
            'email' => "The {$fieldName} must be a valid email address.",
            'min' => "The {$fieldName} must be at least " . ($params[0] ?? '?') . " characters.",
            'max' => "The {$fieldName} must not exceed " . ($params[0] ?? '?') . " characters.",
            'numeric' => "The {$fieldName} must be a number.",
            'alpha' => "The {$fieldName} must contain only letters.",
            'alphanumeric' => "The {$fieldName} must contain only letters and numbers.",
            'url' => "The {$fieldName} must be a valid URL.",
            'confirmed' => "The {$fieldName} confirmation does not match.",
        ];

        return $messages[$rule] ?? "The {$fieldName} is invalid.";
    }

    public function validateCsrf(Session $session): bool
    {
        if (!$this->config->get('csrf_enabled', true)) {
            return true;
        }

        $tokenName = $this->config->get('csrf_token_name', '_token');
        $submittedToken = $_POST[$tokenName] ?? '';
        $sessionToken = $session->getToken();

        if (!hash_equals($sessionToken, $submittedToken)) {
            return false;
        }

        // Rotate token after successful validation (use-once semantics)
        $session->regenerateToken();
        return true;
    }

    public function addRule(string $name, callable $callback): void
    {
        $this->rules[$name] = $callback;
    }
}
