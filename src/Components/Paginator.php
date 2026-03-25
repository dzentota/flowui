<?php

namespace FlowUI\Components;

class Paginator
{
    private int $total;
    private int $perPage;
    private int $currentPage;
    private string $baseUrl;
    private int $maxLinks = 7;

    public function __construct(int $total, int $perPage = 10, int $currentPage = 1)
    {
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
        $this->baseUrl = $this->getCurrentUrl();
    }

    public function setMaxLinks(int $max): self
    {
        $this->maxLinks = $max;
        return $this;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    public function hasPages(): bool
    {
        return $this->getTotalPages() > 1;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function getNextPage(): int
    {
        return min($this->getTotalPages(), $this->currentPage + 1);
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function render(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav class="flow-pagination" role="navigation" aria-label="Pagination">';
        $html .= '<ul class="flow-pagination-list">';
        
        // Previous button
        if ($this->hasPrevious()) {
            $html .= $this->renderLink($this->getPreviousPage(), '‹', 'Previous');
        } else {
            $html .= $this->renderDisabled('‹', 'Previous');
        }
        
        // Page numbers
        $pages = $this->getPageRange();
        foreach ($pages as $page) {
            if ($page === '...') {
                $html .= $this->renderEllipsis();
            } else {
                if ($page === $this->currentPage) {
                    $html .= $this->renderActive($page);
                } else {
                    $html .= $this->renderLink($page, (string)$page);
                }
            }
        }
        
        // Next button
        if ($this->hasNext()) {
            $html .= $this->renderLink($this->getNextPage(), '›', 'Next');
        } else {
            $html .= $this->renderDisabled('›', 'Next');
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }

    private function getPageRange(): array
    {
        $totalPages = $this->getTotalPages();
        
        if ($totalPages <= $this->maxLinks) {
            return range(1, $totalPages);
        }
        
        $pages = [];
        $leftOffset = floor(($this->maxLinks - 3) / 2);
        $rightOffset = ceil(($this->maxLinks - 3) / 2);
        
        // Always show first page
        $pages[] = 1;
        
        if ($this->currentPage <= $leftOffset + 2) {
            // Near the start
            for ($i = 2; $i <= $this->maxLinks - 2; $i++) {
                $pages[] = $i;
            }
            $pages[] = '...';
        } elseif ($this->currentPage >= $totalPages - $rightOffset - 1) {
            // Near the end
            $pages[] = '...';
            for ($i = $totalPages - $this->maxLinks + 3; $i < $totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            // In the middle
            $pages[] = '...';
            for ($i = $this->currentPage - $leftOffset; $i <= $this->currentPage + $rightOffset; $i++) {
                $pages[] = $i;
            }
            $pages[] = '...';
        }
        
        // Always show last page
        $pages[] = $totalPages;
        
        return $pages;
    }

    private function renderLink(int $page, string $label, ?string $ariaLabel = null): string
    {
        $url = $this->buildUrl($page);
        $aria = $ariaLabel ? ' aria-label="' . htmlspecialchars($ariaLabel) . '"' : '';
        
        return sprintf(
            '<li><a href="%s" class="flow-pagination-link"%s>%s</a></li>',
            htmlspecialchars($url),
            $aria,
            htmlspecialchars($label)
        );
    }

    private function renderActive(int $page): string
    {
        return sprintf(
            '<li><span class="flow-pagination-link flow-pagination-active" aria-current="page">%d</span></li>',
            $page
        );
    }

    private function renderDisabled(string $label, string $ariaLabel): string
    {
        return sprintf(
            '<li><span class="flow-pagination-link flow-pagination-disabled" aria-label="%s">%s</span></li>',
            htmlspecialchars($ariaLabel),
            htmlspecialchars($label)
        );
    }

    private function renderEllipsis(): string
    {
        return '<li><span class="flow-pagination-ellipsis">...</span></li>';
    }

    private function buildUrl(int $page): string
    {
        $params = $_GET;
        $params['page'] = $page;
        
        $urlParts = parse_url($this->baseUrl);
        $path = $urlParts['path'] ?? '/';
        $query = http_build_query($params);
        
        return $path . ($query ? '?' . $query : '');
    }

    private function getCurrentUrl(): string
    {
        $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        // Reject paths with traversal sequences; fall back to script name
        if (!is_string($rawPath) || strpos($rawPath, '..') !== false) {
            return isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
        }
        // Preserve query string for URL building, but use the safe path
        $query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
            ? '?' . $_SERVER['QUERY_STRING']
            : '';
        return $rawPath . $query;
    }

    public static function fromRequest(int $total, int $perPage = 10): self
    {
        $page = (int)($_GET['page'] ?? 1);
        return new self($total, $perPage, $page);
    }
}
