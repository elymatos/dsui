{{-- Data Table Component with sorting, filtering, and pagination --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    {{-- Table Header/Controls --}}
    <div class="ds-data-table-header">
        {{-- Search --}}
        <div class="ds-data-table-search" x-show="searchable">
            <div class="ds-search-input">
                <input type="text"
                       class="ds-input"
                       :placeholder="searchPlaceholder"
                       x-model="searchQuery"
                       @input="handleSearch">
                <div class="ds-search-icon">
                    <svg viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        {{-- Controls --}}
        <div class="ds-data-table-controls">
            {{-- Selection Info --}}
            <div class="ds-selection-info" x-show="selection && selectedRows.length > 0">
                <span x-text="`${selectedRows.length} selected`"></span>
                <button type="button" 
                        class="ds-button ds-button--small ds-button--secondary"
                        @click="clearSelection">
                    Clear
                </button>
            </div>
            
            {{-- Export --}}
            <div class="ds-export-controls" x-show="exportable">
                <button type="button"
                        class="ds-button ds-button--small ds-button--secondary"
                        @click="toggleExportMenu"
                        :aria-expanded="showExportMenu">
                    Export
                    <svg viewBox="0 0 16 16" fill="currentColor">
                        <path d="M3.5 2A1.5 1.5 0 0 0 2 3.5v9A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 12.5 2h-9zm5.707 6.293a1 1 0 0 0-1.414 0L6.5 9.586V6a.5.5 0 0 0-1 0v3.586L4.207 8.293a1 1 0 1 0-1.414 1.414l2.5 2.5a1 1 0 0 0 1.414 0l2.5-2.5a1 1 0 0 0 0-1.414z"/>
                    </svg>
                </button>
                
                {{-- Export Menu --}}
                <div class="ds-export-menu" x-show="showExportMenu" x-transition>
                    <template x-for="format in exportFormats" :key="format">
                        <button type="button"
                                class="ds-export-option"
                                @click="exportData(format)"
                                x-text="format.toUpperCase()"></button>
                    </template>
                </div>
            </div>
            
            {{-- Column Visibility --}}
            <button type="button"
                    class="ds-button ds-button--small ds-button--secondary"
                    @click="toggleColumnMenu"
                    :aria-expanded="showColumnMenu">
                Columns
            </button>
        </div>
    </div>
    
    {{-- Column Visibility Menu --}}
    <div class="ds-column-menu" x-show="showColumnMenu" x-transition>
        <template x-for="column in columns" :key="column.key">
            <label class="ds-checkbox-label">
                <input type="checkbox"
                       class="ds-checkbox"
                       :checked="!column.hidden"
                       @change="toggleColumn(column.key)">
                <span x-text="column.label"></span>
            </label>
        </template>
    </div>
    
    {{-- Filter Bar --}}
    <div class="ds-filter-bar" x-show="hasActiveFilters || showFilters">
        <template x-for="column in filterableColumns" :key="column.key">
            <div class="ds-filter-item">
                <label class="ds-filter-label" x-text="column.label"></label>
                <select class="ds-select ds-select--small"
                        x-model="filters[column.key]"
                        @change="applyFilters">
                    <option value="">All</option>
                    <template x-for="option in getFilterOptions(column.key)" :key="option.value">
                        <option :value="option.value" x-text="option.label"></option>
                    </template>
                </select>
            </div>
        </template>
        
        <button type="button"
                class="ds-button ds-button--small ds-button--secondary"
                @click="clearFilters"
                x-show="hasActiveFilters">
            Clear Filters
        </button>
    </div>
    
    {{-- Table Container --}}
    <div class="ds-table-container" 
         :class="{'ds-table-container--virtualized': virtualized}">
        
        {{-- Table --}}
        <table class="ds-table" 
               :id="tableId"
               role="table"
               :aria-rowcount="filteredData.length"
               :aria-colcount="visibleColumns.length">
            
            {{-- Table Header --}}
            <thead class="ds-table-head">
                <tr role="row">
                    {{-- Selection Column --}}
                    <th class="ds-table-header ds-table-header--selection"
                        x-show="selection"
                        role="columnheader">
                        <input type="checkbox"
                               class="ds-checkbox"
                               x-show="selectionMode === 'multiple'"
                               :checked="allSelected"
                               :indeterminate="someSelected"
                               @change="toggleAllSelection"
                               aria-label="Select all rows">
                    </th>
                    
                    {{-- Data Columns --}}
                    <template x-for="column in visibleColumns" :key="column.key">
                        <th class="ds-table-header"
                            :class="[
                                `ds-table-header--${column.align}`,
                                {
                                    'ds-table-header--sortable': column.sortable,
                                    'ds-table-header--sorted': sortBy === column.key,
                                    'ds-table-header--sticky': column.sticky
                                }
                            ]"
                            :style="column.width ? `width: ${column.width}` : ''"
                            role="columnheader"
                            :aria-sort="getSortDirection(column.key)">
                            
                            <div class="ds-table-header-content">
                                <button type="button"
                                        class="ds-table-header-button"
                                        x-show="column.sortable"
                                        @click="handleSort(column.key)"
                                        :aria-label="`Sort by ${column.label}`">
                                    <span x-text="column.label"></span>
                                    <div class="ds-sort-icon">
                                        <svg viewBox="0 0 16 16" fill="currentColor" x-show="sortBy !== column.key">
                                            <path d="M3 9.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                                        </svg>
                                        <svg viewBox="0 0 16 16" fill="currentColor" x-show="sortBy === column.key && sortDirection === 'asc'">
                                            <path d="M3.5 2.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 11.293V2.5zm3.5 1a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zM7.5 6a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zm0 3a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1z"/>
                                        </svg>
                                        <svg viewBox="0 0 16 16" fill="currentColor" x-show="sortBy === column.key && sortDirection === 'desc'">
                                            <path d="M3.5 13.5a.5.5 0 0 1-1 0V4.707L1.354 5.854a.5.5 0 1 1-.708-.708l2-1.999.007-.007a.497.497 0 0 1 .7.006l2 2a.5.5 0 1 1-.707.708L3.5 4.707V13.5zm3.5-9a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zM7.5 6a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zm0 3a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1h-1z"/>
                                        </svg>
                                    </div>
                                </button>
                                
                                <span x-show="!column.sortable" x-text="column.label"></span>
                            </div>
                        </th>
                    </template>
                </tr>
            </thead>
            
            {{-- Table Body --}}
            <tbody class="ds-table-body">
                <template x-for="(row, index) in paginatedData" :key="row.id">
                    <tr class="ds-table-row"
                        :class="[
                            {
                                'ds-table-row--selected': row.selected,
                                'ds-table-row--disabled': row.disabled,
                                'ds-table-row--clickable': rowClickable
                            },
                            row.variant ? `ds-table-row--${row.variant}` : ''
                        ]"
                        role="row"
                        :aria-rowindex="index + 1"
                        :aria-selected="selection ? row.selected : null"
                        @click="handleRowClick(row, $event)">
                        
                        {{-- Selection Cell --}}
                        <td class="ds-table-cell ds-table-cell--selection"
                            x-show="selection"
                            role="gridcell">
                            <input type="checkbox"
                                   class="ds-checkbox"
                                   :type="selectionMode === 'single' ? 'radio' : 'checkbox'"
                                   :checked="row.selected"
                                   @change="toggleRowSelection(row.id, $event)"
                                   :disabled="row.disabled"
                                   :aria-label="`Select row ${index + 1}`">
                        </td>
                        
                        {{-- Data Cells --}}
                        <template x-for="column in visibleColumns" :key="column.key">
                            <td class="ds-table-cell"
                                :class="[
                                    `ds-table-cell--${column.align}`,
                                    `ds-table-cell--${column.type}`,
                                    {'ds-table-cell--sticky': column.sticky}
                                ]"
                                role="gridcell">
                                <div class="ds-table-cell-content"
                                     x-html="formatCellValue(row.data[column.key], column)"></div>
                            </td>
                        </template>
                    </tr>
                </template>
                
                {{-- No Data Row --}}
                <tr class="ds-table-row ds-table-row--no-data" x-show="filteredData.length === 0">
                    <td class="ds-table-cell ds-table-cell--no-data"
                        :colspan="visibleColumns.length + (selection ? 1 : 0)">
                        <div class="ds-no-data">
                            <div class="ds-no-data-icon">📄</div>
                            <div class="ds-no-data-message">No data available</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    {{-- Table Footer/Pagination --}}
    <div class="ds-data-table-footer" x-show="pagination && filteredData.length > 0">
        <div class="ds-pagination-info">
            <span x-text="`Showing ${paginationStart} to ${paginationEnd} of ${filteredData.length} entries`"></span>
        </div>
        
        <div class="ds-pagination-controls">
            <button type="button"
                    class="ds-button ds-button--small ds-button--secondary"
                    @click="goToPage(1)"
                    :disabled="currentPage === 1"
                    aria-label="First page">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8.354 1.646a.5.5 0 0 1 0 .708L2.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    <path d="M12.354 1.646a.5.5 0 0 1 0 .708L6.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>
            
            <button type="button"
                    class="ds-button ds-button--small ds-button--secondary"
                    @click="previousPage"
                    :disabled="currentPage === 1"
                    aria-label="Previous page">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>
            
            <span class="ds-pagination-current" x-text="`Page ${currentPage} of ${totalPages}`"></span>
            
            <button type="button"
                    class="ds-button ds-button--small ds-button--secondary"
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    aria-label="Next page">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
            
            <button type="button"
                    class="ds-button ds-button--small ds-button--secondary"
                    @click="goToPage(totalPages)"
                    :disabled="currentPage === totalPages"
                    aria-label="Last page">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    <path d="M8.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L14.293 8 8.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
        
        <div class="ds-per-page-controls">
            <label for="per-page-select">Show</label>
            <select id="per-page-select"
                    class="ds-select ds-select--small"
                    x-model="perPage"
                    @change="handlePerPageChange">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>per page</span>
        </div>
    </div>
    
    {{-- Default slot content --}}
    @if(empty($data))
        <div class="ds-data-table-default-content">
            {{ $slot }}
        </div>
    @endif
</div>