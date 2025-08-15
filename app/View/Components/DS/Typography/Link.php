<?php

namespace App\View\Components\DS\Typography;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class Link extends BaseComponent
{
    public string $href;
    public ?string $target;
    public ?string $rel;
    public bool $external;
    public bool $download;
    public ?string $downloadName;
    public string $underline;
    public bool $visited;
    public ?string $icon;
    public string $iconPosition;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        string $href = '#',
        ?string $target = null,
        ?string $rel = null,
        bool $external = false,
        bool $download = false,
        ?string $downloadName = null,
        string $underline = 'hover',
        bool $visited = true,
        ?string $icon = null,
        string $iconPosition = 'right'
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->href = $href;
        $this->target = $target;
        $this->rel = $rel;
        $this->external = $external;
        $this->download = $download;
        $this->downloadName = $downloadName;
        $this->underline = $underline;
        $this->visited = $visited;
        $this->icon = $icon;
        $this->iconPosition = $iconPosition;
        
        // Auto-detect external links
        if (!$this->external && $this->isExternalUrl($href)) {
            $this->external = true;
        }
        
        // Set default target and rel for external links
        if ($this->external && !$this->target) {
            $this->target = '_blank';
        }
        
        if ($this->external && !$this->rel) {
            $this->rel = 'noopener noreferrer';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.typography.link');
    }

    /**
     * Get base CSS classes specific to link component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-link'];
        
        // Underline behavior
        if ($this->underline !== 'hover') {
            $classes[] = "ds-link--underline-{$this->underline}";
        }
        
        // External link styling
        if ($this->external) {
            $classes[] = 'ds-link--external';
        }
        
        // Download link styling
        if ($this->download) {
            $classes[] = 'ds-link--download';
        }
        
        // Visited link styling
        if (!$this->visited) {
            $classes[] = 'ds-link--no-visited';
        }
        
        // Icon position
        if ($this->icon) {
            $classes[] = "ds-link--icon-{$this->iconPosition}";
        }
        
        return $classes;
    }

    /**
     * Get link attributes.
     */
    public function getLinkAttributes(): array
    {
        $attributes = ['href' => $this->href];
        
        if ($this->target) {
            $attributes['target'] = $this->target;
        }
        
        if ($this->rel) {
            $attributes['rel'] = $this->rel;
        }
        
        if ($this->download) {
            $attributes['download'] = $this->downloadName ?: true;
        }
        
        return $attributes;
    }

    /**
     * Check if URL is external.
     */
    protected function isExternalUrl(string $url): bool
    {
        // Skip if it's a fragment, relative path, or mailto/tel
        if (str_starts_with($url, '#') || 
            str_starts_with($url, '/') || 
            str_starts_with($url, 'mailto:') || 
            str_starts_with($url, 'tel:')) {
            return false;
        }
        
        // Check if it has a different domain
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return false;
        }
        
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        return $parsed['host'] !== $currentHost;
    }

    /**
     * Get the appropriate icon for the link type.
     */
    public function getIcon(): ?string
    {
        if ($this->icon) {
            return $this->icon;
        }
        
        if ($this->external) {
            return 'external-link';
        }
        
        if ($this->download) {
            return 'download';
        }
        
        // Auto-detect based on URL
        if (str_starts_with($this->href, 'mailto:')) {
            return 'mail';
        }
        
        if (str_starts_with($this->href, 'tel:')) {
            return 'phone';
        }
        
        return null;
    }

    /**
     * Get icon HTML.
     */
    public function getIconHtml(): string
    {
        $iconName = $this->getIcon();
        if (!$iconName) {
            return '';
        }
        
        $iconMap = [
            'external-link' => '<svg class="ds-link-icon" viewBox="0 0 16 16" fill="currentColor"><path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>',
            'download' => '<svg class="ds-link-icon" viewBox="0 0 16 16" fill="currentColor"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>',
            'mail' => '<svg class="ds-link-icon" viewBox="0 0 16 16" fill="currentColor"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/></svg>',
            'phone' => '<svg class="ds-link-icon" viewBox="0 0 16 16" fill="currentColor"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122L9.98 10.94a.678.678 0 0 1-.543-.18L6.77 8.092a.678.678 0 0 1-.18-.543l.509-1.805a.678.678 0 0 0-.122-.58L5.183 2.857a.678.678 0 0 0-.529-.003L3.654 1.328z"/></svg>'
        ];
        
        return $iconMap[$iconName] ?? '';
    }

    /**
     * Get valid underline options.
     */
    public function getValidUnderlines(): array
    {
        return ['none', 'hover', 'always'];
    }

    /**
     * Get valid icon positions.
     */
    public function getValidIconPositions(): array
    {
        return ['left', 'right'];
    }

    /**
     * Get accessibility attributes specific to links.
     */
    protected function getAccessibilityAttributes(): array
    {
        $attributes = parent::getAccessibilityAttributes();
        
        // Add ARIA label for external links
        if ($this->external && !isset($attributes['aria-label'])) {
            $attributes['aria-label'] = 'Opens in new window';
        }
        
        // Add ARIA label for download links
        if ($this->download && !isset($attributes['aria-label'])) {
            $fileName = $this->downloadName ?: 'file';
            $attributes['aria-label'] = "Download {$fileName}";
        }
        
        return $attributes;
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validUnderlines = $this->getValidUnderlines();
        if (!in_array($this->underline, $validUnderlines)) {
            throw new \InvalidArgumentException(
                "Invalid underline '{$this->underline}'. Valid options: " . implode(', ', $validUnderlines)
            );
        }
        
        $validIconPositions = $this->getValidIconPositions();
        if (!in_array($this->iconPosition, $validIconPositions)) {
            throw new \InvalidArgumentException(
                "Invalid icon position '{$this->iconPosition}'. Valid positions: " . implode(', ', $validIconPositions)
            );
        }
    }
}