<?php

namespace FlowUI\Processors;

use FlowUI\Core\Config;
use DOMDocument;
use DOMElement;
use DOMXPath;

class TableProcessor implements ProcessorInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        
        // Find all tables with sortable headers
        $tables = $xpath->query('//table[.//th[@sortable or @data-sortable]]');

        foreach ($tables as $table) {
            $this->processTable($table, $dom, $xpath);
        }
    }

    private function processTable(DOMElement $table, DOMDocument $dom, DOMXPath $xpath): void
    {
        // Add table class
        $table->setAttribute('class', trim($table->getAttribute('class') . ' flow-sortable-table'));
        
        // Find sortable headers
        $headers = $xpath->query('.//th[@sortable or @data-sortable]', $table);
        
        foreach ($headers as $header) {
            $this->processSortableHeader($header, $dom);
        }
    }

    private function processSortableHeader(DOMElement $header, DOMDocument $dom): void
    {
        // Get column name
        $column = $header->getAttribute('data-column') ?: 
                  $header->getAttribute('sortable') ?:
                  strtolower(preg_replace('/\s+/', '_', trim($header->textContent)));
        
        // Get current sort from GET parameters
        $currentSort = $_GET['sort'] ?? null;
        $currentDir = $_GET['dir'] ?? 'asc';
        
        // Determine next direction
        $nextDir = 'asc';
        $isActive = false;
        if ($currentSort === $column) {
            $isActive = true;
            $nextDir = $currentDir === 'asc' ? 'desc' : 'asc';
        }
        
        // Build URL manually to avoid double encoding
        $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        // Reject paths that contain traversal sequences or are otherwise invalid
        $urlPath = (is_string($rawPath) && strpos($rawPath, '..') === false)
            ? $rawPath
            : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/');
        
        // Build query parameters array (use $_GET instead of parsing REQUEST_URI)
        $params = [];
        $params['sort'] = $column;
        $params['dir'] = $nextDir;
        
        // Add other GET parameters from $_GET (already decoded by PHP)
        foreach ($_GET as $key => $value) {
            if ($key !== 'sort' && $key !== 'dir') {
                $params[$key] = $value;
            }
        }
        
        // Build query string - let http_build_query handle encoding
        $sortUrl = $urlPath . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        // Create wrapper link
        $link = $dom->createElement('a');
        $link->setAttribute('href', $sortUrl);
        $link->setAttribute('class', 'flow-sort-link');
        $link->setAttribute('data-column', $column);
        
        if ($isActive) {
            $link->setAttribute('data-active', 'true');
            $link->setAttribute('data-direction', $currentDir);
        }
        
        // Move header content into link
        while ($header->firstChild) {
            $link->appendChild($header->firstChild);
        }
        
        // Add sort indicator
        $indicator = $dom->createElement('span');
        $indicator->setAttribute('class', 'flow-sort-indicator');
        
        if ($isActive) {
            $arrow = $currentDir === 'asc' ? ' ↑' : ' ↓';
            $indicator->nodeValue = $arrow;
        } else {
            $indicator->nodeValue = ' ↕';
        }
        
        $link->appendChild($indicator);
        $header->appendChild($link);
        
        // Add header class
        $header->setAttribute('class', trim($header->getAttribute('class') . ' flow-sortable'));
        if ($isActive) {
            $header->setAttribute('class', trim($header->getAttribute('class') . ' flow-sorted-' . $currentDir));
        }
    }
}
