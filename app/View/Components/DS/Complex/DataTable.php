<?php

namespace App\View\Components\DS\Complex;

use App\View\Components\DS\BaseComponent;
use Illuminate\View\View;

class DataTable extends BaseComponent
{
    public array $columns;
    public array $data;
    public array $sortable;
    public array $filterable;
    public bool $pagination;
    public int $perPage;
    public bool $selection;
    public string $selectionMode;
    public bool $searchable;
    public string $searchPlaceholder;
    public bool $exportable;
    public array $exportFormats;
    public bool $responsive;
    public string $responsiveMode;
    public bool $virtualized;
    public int $rowHeight;
    public bool $striped;
    public bool $bordered;
    public bool $hoverable;
    public bool $compact;

    public function __construct(
        string $variant = 'primary',
        string $size = 'normal',
        bool $disabled = false,
        bool $loading = false,
        array $alpineData = [],
        ?string $htmxAction = null,
        array $columns = [],
        array $data = [],
        array $sortable = [],
        array $filterable = [],
        bool $pagination = true,
        int $perPage = 25,
        bool $selection = false,
        string $selectionMode = 'multiple',
        bool $searchable = true,
        string $searchPlaceholder = 'Search...',
        bool $exportable = false,
        array $exportFormats = ['csv', 'json'],
        bool $responsive = true,
        string $responsiveMode = 'scroll',
        bool $virtualized = false,
        int $rowHeight = 48,
        bool $striped = true,
        bool $bordered = true,
        bool $hoverable = true,
        bool $compact = false
    ) {
        parent::__construct($variant, $size, $disabled, $loading, $alpineData, $htmxAction);
        
        $this->columns = $columns;
        $this->data = $data;
        $this->sortable = $sortable;
        $this->filterable = $filterable;
        $this->pagination = $pagination;
        $this->perPage = $perPage;
        $this->selection = $selection;
        $this->selectionMode = $selectionMode;
        $this->searchable = $searchable;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->exportable = $exportable;
        $this->exportFormats = $exportFormats;
        $this->responsive = $responsive;
        $this->responsiveMode = $responsiveMode;
        $this->virtualized = $virtualized;
        $this->rowHeight = $rowHeight;
        $this->striped = $striped;
        $this->bordered = $bordered;
        $this->hoverable = $hoverable;
        $this->compact = $compact;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.ds.complex.data-table');
    }

    /**
     * Get base CSS classes specific to data table component.
     */
    protected function getBaseClasses(): array
    {
        $classes = ['ds-component', 'ds-data-table'];
        
        // Table styling
        if ($this->striped) {
            $classes[] = 'ds-data-table--striped';
        }
        
        if ($this->bordered) {
            $classes[] = 'ds-data-table--bordered';
        }
        
        if ($this->hoverable) {
            $classes[] = 'ds-data-table--hoverable';
        }
        
        if ($this->compact) {
            $classes[] = 'ds-data-table--compact';
        }
        
        // Responsive mode
        if ($this->responsive) {
            $classes[] = 'ds-data-table--responsive';
            $classes[] = "ds-data-table--{$this->responsiveMode}";
        }
        
        // Selection
        if ($this->selection) {
            $classes[] = 'ds-data-table--selectable';
            $classes[] = "ds-data-table--selection-{$this->selectionMode}";
        }
        
        // Virtualization
        if ($this->virtualized) {
            $classes[] = 'ds-data-table--virtualized';
        }
        
        return $classes;
    }

    /**
     * Get formatted columns data.
     */
    public function getFormattedColumns(): array
    {
        return collect($this->columns)->map(function ($column, $index) {
            $key = is_array($column) ? ($column['key'] ?? $index) : $index;
            
            return [
                'key' => $key,
                'label' => is_array($column) ? ($column['label'] ?? ucfirst($key)) : $column,
                'sortable' => in_array($key, $this->sortable) || (is_array($column) && ($column['sortable'] ?? false)),
                'filterable' => in_array($key, $this->filterable) || (is_array($column) && ($column['filterable'] ?? false)),
                'width' => is_array($column) ? ($column['width'] ?? null) : null,
                'align' => is_array($column) ? ($column['align'] ?? 'left') : 'left',
                'type' => is_array($column) ? ($column['type'] ?? 'text') : 'text',
                'format' => is_array($column) ? ($column['format'] ?? null) : null,
                'hidden' => is_array($column) ? ($column['hidden'] ?? false) : false,
                'resizable' => is_array($column) ? ($column['resizable'] ?? true) : true,
                'sticky' => is_array($column) ? ($column['sticky'] ?? false) : false,
            ];
        })->toArray();
    }

    /**
     * Get formatted data with row metadata.
     */
    public function getFormattedData(): array
    {
        return collect($this->data)->map(function ($row, $index) {
            return [
                'id' => $row['id'] ?? $index,
                'data' => $row,
                'selected' => false,
                'expanded' => false,
                'disabled' => $row['disabled'] ?? false,
                'variant' => $row['variant'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Get valid selection modes.
     */
    public function getValidSelectionModes(): array
    {
        return ['single', 'multiple'];
    }

    /**
     * Get valid responsive modes.
     */
    public function getValidResponsiveModes(): array
    {
        return ['scroll', 'stack', 'collapse'];
    }

    /**
     * Get valid export formats.
     */
    public function getValidExportFormats(): array
    {
        return ['csv', 'json', 'xlsx', 'pdf'];
    }

    /**
     * Get Alpine.js configuration for the data table component.
     */
    public function getAlpineConfig(): array
    {
        return array_merge([
            'columns' => $this->getFormattedColumns(),
            'originalData' => $this->getFormattedData(),
            'pagination' => $this->pagination,
            'perPage' => $this->perPage,
            'selection' => $this->selection,
            'selectionMode' => $this->selectionMode,
            'searchable' => $this->searchable,
            'searchPlaceholder' => $this->searchPlaceholder,
            'exportable' => $this->exportable,
            'exportFormats' => $this->exportFormats,
            'responsive' => $this->responsive,
            'responsiveMode' => $this->responsiveMode,
            'virtualized' => $this->virtualized,
            'rowHeight' => $this->rowHeight,
        ], $this->alpineData);
    }

    /**
     * Generate Alpine.js attributes for the component.
     */
    public function getAlpineAttributes(): array
    {
        $config = json_encode($this->getAlpineConfig());
        
        return [
            'x-data' => "DS.component.dataTable({$config})"
        ];
    }

    /**
     * Get unique ID for table.
     */
    public function getTableId(): string
    {
        return 'data-table-' . uniqid();
    }

    /**
     * Validate component props.
     */
    protected function validateProps(): void
    {
        parent::validateProps();
        
        $validSelectionModes = $this->getValidSelectionModes();
        if (!in_array($this->selectionMode, $validSelectionModes)) {
            throw new \InvalidArgumentException(
                "Invalid selection mode '{$this->selectionMode}'. Valid modes: " . implode(', ', $validSelectionModes)
            );
        }
        
        $validResponsiveModes = $this->getValidResponsiveModes();
        if (!in_array($this->responsiveMode, $validResponsiveModes)) {
            throw new \InvalidArgumentException(
                "Invalid responsive mode '{$this->responsiveMode}'. Valid modes: " . implode(', ', $validResponsiveModes)
            );
        }
        
        $validExportFormats = $this->getValidExportFormats();
        foreach ($this->exportFormats as $format) {
            if (!in_array($format, $validExportFormats)) {
                throw new \InvalidArgumentException(
                    "Invalid export format '{$format}'. Valid formats: " . implode(', ', $validExportFormats)
                );
            }
        }
        
        if ($this->perPage < 1) {
            throw new \InvalidArgumentException('perPage must be at least 1');
        }
        
        if ($this->rowHeight < 1) {
            throw new \InvalidArgumentException('rowHeight must be at least 1');
        }
    }
}