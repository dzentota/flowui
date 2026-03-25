<?php

namespace FlowUI\Processors;

use FlowUI\Core\Config;
use DOMDocument;
use DOMElement;
use DOMXPath;

class DropdownProcessor implements ProcessorInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        
        // Find all elements with data-toggle="dropdown"
        $triggers = $xpath->query('//*[@data-toggle="dropdown"]');

        foreach ($triggers as $trigger) {
            $this->processDropdown($trigger, $dom, $xpath);
        }
    }

    private function processDropdown(DOMElement $trigger, DOMDocument $dom, DOMXPath $xpath): void
    {
        // Generate unique ID if not present
        if (!$trigger->hasAttribute('id')) {
            $trigger->setAttribute('id', 'dropdown-trigger-' . bin2hex(random_bytes(8)));
        }

        $triggerId = $trigger->getAttribute('id');
        
        // Add ARIA attributes
        $trigger->setAttribute('aria-haspopup', 'true');
        $trigger->setAttribute('aria-expanded', 'false');
        
        // Find the menu element (next sibling or specified by data-target)
        $menu = null;
        if ($trigger->hasAttribute('data-target')) {
            $targetId = $trigger->getAttribute('data-target');
            // Only allow safe HTML ID characters to prevent XPath injection
            if (preg_match('/^[A-Za-z0-9_\-:.]+$/', $targetId)) {
                $menus = $xpath->query("//*[@id='{$targetId}']");
                if ($menus !== false && $menus->length > 0) {
                    $menu = $menus->item(0);
                }
            }
        }
        
        if ($menu === null) {
            // Look for next sibling menu or ul
            $next = $trigger->nextSibling;
            while ($next) {
                if ($next instanceof DOMElement && 
                    ($next->nodeName === 'menu' || $next->nodeName === 'ul')) {
                    $menu = $next;
                    break;
                }
                $next = $next->nextSibling;
            }
        }

        if ($menu) {
            // Generate ID for menu if needed
            if (!$menu->hasAttribute('id')) {
                $menu->setAttribute('id', 'dropdown-menu-' . bin2hex(random_bytes(8)));
            }
            
            $menuId = $menu->getAttribute('id');
            
            // Add dropdown classes and attributes
            $menu->setAttribute('class', trim($menu->getAttribute('class') . ' flow-dropdown-menu'));
            $menu->setAttribute('data-dropdown-menu', 'true');
            $menu->setAttribute('aria-labelledby', $triggerId);
            $menu->setAttribute('role', 'menu');
            
            // Link trigger to menu
            $trigger->setAttribute('data-dropdown-menu', $menuId);
            
            // Add classes to trigger
            $trigger->setAttribute('class', trim($trigger->getAttribute('class') . ' flow-dropdown-trigger'));
        }
    }
}
