<?php

namespace FlowUI\Forms;

use FlowUI\Core\Session;
use FlowUI\Core\Config;
use FlowUI\Processors\ProcessorInterface;
use DOMDocument;
use DOMElement;
use DOMXPath;

class FormProcessor implements ProcessorInterface
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
        $forms = $xpath->query('//form[@method="POST" or @method="post"]');

        $validationRules = [];

        foreach ($forms as $form) {
            if ($this->config->get('csrf_enabled', true)) {
                $this->injectCsrfToken($form, $dom);
            }

            $inputs = $xpath->query('.//input[@data-rules] | .//textarea[@data-rules] | .//select[@data-rules]', $form);
            
            foreach ($inputs as $input) {
                $name = $input->getAttribute('name');
                $rules = $input->getAttribute('data-rules');
                
                if ($name && $rules) {
                    $validationRules[$name] = $rules;
                    $this->restoreOldValue($input);
                    $this->injectErrors($input, $dom);
                }
            }
        }

        if (!empty($validationRules)) {
            $this->session->set('_flow_validation_rules', $validationRules);
        }

        $this->session->clearFlash();
    }

    private function injectCsrfToken(DOMElement $form, DOMDocument $dom): void
    {
        $tokenName = $this->config->get('csrf_token_name', '_token');
        $token = $this->session->getToken();

        $existing = null;
        foreach ($form->childNodes as $child) {
            if ($child instanceof DOMElement && 
                $child->nodeName === 'input' && 
                $child->getAttribute('name') === $tokenName) {
                $existing = $child;
                break;
            }
        }

        if ($existing) {
            $existing->setAttribute('value', $token);
        } else {
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', $tokenName);
            $input->setAttribute('value', $token);
            
            if ($form->firstChild) {
                $form->insertBefore($input, $form->firstChild);
            } else {
                $form->appendChild($input);
            }
        }
    }

    private function restoreOldValue(DOMElement $input): void
    {
        $old = $this->session->getFlash('_old', []);
        $name = $input->getAttribute('name');
        
        if (!isset($old[$name])) {
            return;
        }

        $value = $old[$name];
        $tagName = strtolower($input->nodeName);

        if ($tagName === 'input') {
            $type = strtolower($input->getAttribute('type'));
            
            if ($type === 'checkbox') {
                if ($value) {
                    $input->setAttribute('checked', 'checked');
                }
            } elseif ($type === 'radio') {
                if ($input->getAttribute('value') === $value) {
                    $input->setAttribute('checked', 'checked');
                }
            } else {
                $input->setAttribute('value', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        } elseif ($tagName === 'textarea') {
            $input->nodeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        } elseif ($tagName === 'select') {
            $options = $input->getElementsByTagName('option');
            foreach ($options as $option) {
                if ($option->getAttribute('value') === $value) {
                    $option->setAttribute('selected', 'selected');
                }
            }
        }
    }

    private function injectErrors(DOMElement $input, DOMDocument $dom): void
    {
        $errors = $this->session->getFlash('_errors', []);
        $name = $input->getAttribute('name');
        
        if (!isset($errors[$name])) {
            return;
        }

        $input->setAttribute('class', trim($input->getAttribute('class') . ' flow-error-field'));

        $errorDiv = $dom->createElement('div');
        $errorDiv->setAttribute('class', 'flow-error');
        
        if (is_array($errors[$name])) {
            $errorText = implode(', ', $errors[$name]);
        } else {
            $errorText = $errors[$name];
        }
        
        $errorDiv->nodeValue = htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8');

        if ($input->parentNode && $input->nextSibling) {
            $input->parentNode->insertBefore($errorDiv, $input->nextSibling);
        } elseif ($input->parentNode) {
            $input->parentNode->appendChild($errorDiv);
        }
    }
}
