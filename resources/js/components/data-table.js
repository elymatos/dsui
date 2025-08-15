// DSUI Data Table Component
DS.component.dataTable = (config = {}) => ({
    ...DS.component.base(config),
    
    // Core data
    columns: config.columns || [],
    originalData: config.originalData || [],
    filteredData: [],
    paginatedData: [],
    
    // State
    searchQuery: '',
    sortBy: '',
    sortDirection: 'asc',
    filters: {},
    currentPage: 1,
    perPage: config.perPage || 25,
    
    // Selection
    selection: config.selection || false,
    selectionMode: config.selectionMode || 'multiple',
    selectedRows: [],
    
    // UI state
    showExportMenu: false,
    showColumnMenu: false,
    showFilters: false,
    
    // Configuration
    searchable: config.searchable !== undefined ? config.searchable : true,
    searchPlaceholder: config.searchPlaceholder || 'Search...',
    pagination: config.pagination !== undefined ? config.pagination : true,
    exportable: config.exportable || false,
    exportFormats: config.exportFormats || ['csv', 'json'],
    responsive: config.responsive !== undefined ? config.responsive : true,
    responsiveMode: config.responsiveMode || 'scroll',
    virtualized: config.virtualized || false,
    rowHeight: config.rowHeight || 48,
    
    // Internal state
    tableId: 'data-table-' + Math.random().toString(36).substr(2, 9),
    resizeObserver: null,
    virtualScrollTop: 0,
    virtualItemHeight: 48,
    visibleStartIndex: 0,
    visibleEndIndex: 10,
    
    init() {
        DS.component.base(config).init.call(this);
        
        // Initialize data
        this.filteredData = [...this.originalData];
        this.updatePagination();
        
        // Setup responsive behavior
        if (this.responsive) {
            this.setupResponsive();
        }
        
        // Setup virtualization
        if (this.virtualized) {
            this.setupVirtualization();
        }
        
        // Close menus when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.$el.contains(e.target)) {
                this.showExportMenu = false;
                this.showColumnMenu = false;
            }
        });
        
        console.log('DSUI: Data Table initialized', {
            rows: this.originalData.length,
            columns: this.columns.length,
            pagination: this.pagination
        });
    },
    
    // Computed properties
    get visibleColumns() {
        return this.columns.filter(column => !column.hidden);
    },
    
    get filterableColumns() {
        return this.columns.filter(column => column.filterable);
    },
    
    get hasActiveFilters() {
        return Object.values(this.filters).some(value => value !== '' && value !== null);
    },
    
    get totalPages() {
        return Math.ceil(this.filteredData.length / this.perPage);
    },
    
    get paginationStart() {
        return this.filteredData.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
    },
    
    get paginationEnd() {
        return Math.min(this.currentPage * this.perPage, this.filteredData.length);
    },
    
    get allSelected() {
        return this.filteredData.length > 0 && 
               this.filteredData.every(row => row.selected || row.disabled);
    },
    
    get someSelected() {
        return this.selectedRows.length > 0 && !this.allSelected;
    },
    
    get rowClickable() {
        return this.selection || config.onRowClick;
    },
    
    // Search functionality
    handleSearch() {
        this.applyFilters();
        this.currentPage = 1;
        this.updatePagination();
    },
    
    // Sorting functionality
    handleSort(columnKey) {
        if (this.sortBy === columnKey) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortBy = columnKey;
            this.sortDirection = 'asc';
        }
        
        this.applySorting();
        this.updatePagination();
        
        this.$dispatch('ds-table-sort', {
            column: columnKey,
            direction: this.sortDirection
        });
    },
    
    applySorting() {
        if (!this.sortBy) return;
        
        const column = this.columns.find(col => col.key === this.sortBy);
        if (!column) return;
        
        this.filteredData.sort((a, b) => {
            let aVal = a.data[this.sortBy];
            let bVal = b.data[this.sortBy];
            
            // Handle different data types
            if (column.type === 'number') {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else if (column.type === 'date') {
                aVal = new Date(aVal);
                bVal = new Date(bVal);
            } else {
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
            }
            
            let result = 0;
            if (aVal < bVal) result = -1;
            else if (aVal > bVal) result = 1;
            
            return this.sortDirection === 'desc' ? -result : result;
        });
    },
    
    getSortDirection(columnKey) {
        if (this.sortBy !== columnKey) return 'none';
        return this.sortDirection === 'asc' ? 'ascending' : 'descending';
    },
    
    // Filtering functionality
    applyFilters() {
        this.filteredData = this.originalData.filter(row => {
            // Search filter
            if (this.searchQuery) {
                const searchLower = this.searchQuery.toLowerCase();
                const rowMatches = this.visibleColumns.some(column => {
                    const value = String(row.data[column.key] || '').toLowerCase();
                    return value.includes(searchLower);
                });
                if (!rowMatches) return false;
            }
            
            // Column filters
            for (const [columnKey, filterValue] of Object.entries(this.filters)) {
                if (filterValue && filterValue !== '') {
                    const cellValue = String(row.data[columnKey] || '');
                    if (cellValue !== filterValue) return false;
                }
            }
            
            return true;
        });
        
        // Reapply sorting
        this.applySorting();
        
        this.$dispatch('ds-table-filter', {
            searchQuery: this.searchQuery,
            filters: this.filters,
            resultCount: this.filteredData.length
        });
    },
    
    clearFilters() {
        this.searchQuery = '';
        this.filters = {};
        this.applyFilters();
        this.currentPage = 1;
        this.updatePagination();
    },
    
    getFilterOptions(columnKey) {
        const uniqueValues = [...new Set(
            this.originalData.map(row => row.data[columnKey])
                .filter(value => value !== null && value !== undefined && value !== '')
        )].sort();
        
        return uniqueValues.map(value => ({
            value: String(value),
            label: String(value)
        }));
    },
    
    // Pagination functionality
    updatePagination() {
        const start = (this.currentPage - 1) * this.perPage;
        const end = start + this.perPage;
        this.paginatedData = this.filteredData.slice(start, end);
        
        // Update selection state for paginated data
        this.updateSelectionState();
    },
    
    goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
            this.updatePagination();
            
            this.$dispatch('ds-table-page-change', {
                page: page,
                totalPages: this.totalPages
            });
        }
    },
    
    nextPage() {
        this.goToPage(this.currentPage + 1);
    },
    
    previousPage() {
        this.goToPage(this.currentPage - 1);
    },
    
    handlePerPageChange() {
        this.currentPage = 1;
        this.updatePagination();
        
        this.$dispatch('ds-table-per-page-change', {
            perPage: this.perPage
        });
    },
    
    // Selection functionality
    toggleRowSelection(rowId, event) {
        const row = this.filteredData.find(r => r.id === rowId);
        if (!row || row.disabled) return;
        
        if (this.selectionMode === 'single') {
            // Clear all selections first
            this.filteredData.forEach(r => r.selected = false);
            this.selectedRows = [];
        }
        
        row.selected = event.target.checked;
        
        if (row.selected) {
            if (!this.selectedRows.includes(rowId)) {
                this.selectedRows.push(rowId);
            }
        } else {
            this.selectedRows = this.selectedRows.filter(id => id !== rowId);
        }
        
        this.updateSelectionState();
        
        this.$dispatch('ds-table-selection-change', {
            selectedRows: this.selectedRows,
            row: row
        });
    },
    
    toggleAllSelection(event) {
        const checked = event.target.checked;
        
        this.filteredData.forEach(row => {
            if (!row.disabled) {
                row.selected = checked;
            }
        });
        
        this.selectedRows = checked 
            ? this.filteredData.filter(r => !r.disabled).map(r => r.id)
            : [];
        
        this.updateSelectionState();
        
        this.$dispatch('ds-table-selection-change', {
            selectedRows: this.selectedRows,
            selectAll: checked
        });
    },
    
    clearSelection() {
        this.filteredData.forEach(row => row.selected = false);
        this.selectedRows = [];
        this.updateSelectionState();
        
        this.$dispatch('ds-table-selection-clear');
    },
    
    updateSelectionState() {
        // Update row selection states based on selectedRows array
        this.filteredData.forEach(row => {
            row.selected = this.selectedRows.includes(row.id);
        });
    },
    
    // Row interactions
    handleRowClick(row, event) {
        // Don't trigger if clicking on interactive elements
        if (event.target.matches('input, button, a, [role="button"]')) {
            return;
        }
        
        if (this.selection && !row.disabled) {
            // Toggle selection on row click
            const checkbox = event.currentTarget.querySelector('input[type="checkbox"], input[type="radio"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                this.toggleRowSelection(row.id, { target: checkbox });
            }
        }
        
        if (config.onRowClick) {
            config.onRowClick(row, event);
        }
        
        this.$dispatch('ds-table-row-click', { row, event });
    },
    
    // Column management
    toggleColumn(columnKey) {
        const column = this.columns.find(col => col.key === columnKey);
        if (column) {
            column.hidden = !column.hidden;
            
            this.$dispatch('ds-table-column-toggle', {
                column: columnKey,
                hidden: column.hidden
            });
        }
    },
    
    toggleColumnMenu() {
        this.showColumnMenu = !this.showColumnMenu;
        this.showExportMenu = false;
    },
    
    // Export functionality
    toggleExportMenu() {
        this.showExportMenu = !this.showExportMenu;
        this.showColumnMenu = false;
    },
    
    exportData(format) {
        const data = this.selectedRows.length > 0 
            ? this.filteredData.filter(row => row.selected)
            : this.filteredData;
        
        const exportData = data.map(row => {
            const exportRow = {};
            this.visibleColumns.forEach(column => {
                exportRow[column.label] = row.data[column.key];
            });
            return exportRow;
        });
        
        switch (format.toLowerCase()) {
            case 'csv':
                this.exportAsCSV(exportData);
                break;
            case 'json':
                this.exportAsJSON(exportData);
                break;
            case 'xlsx':
                // Would require additional library
                console.warn('XLSX export requires additional library');
                break;
            default:
                console.warn(`Export format ${format} not implemented`);
        }
        
        this.showExportMenu = false;
        
        this.$dispatch('ds-table-export', {
            format,
            data: exportData,
            rowCount: exportData.length
        });
    },
    
    exportAsCSV(data) {
        if (data.length === 0) return;
        
        const headers = Object.keys(data[0]);
        const csvContent = [
            headers.join(','),
            ...data.map(row => 
                headers.map(header => 
                    `"${String(row[header] || '').replace(/"/g, '""')}"`
                ).join(',')
            )
        ].join('\n');
        
        this.downloadFile(csvContent, 'data-export.csv', 'text/csv');
    },
    
    exportAsJSON(data) {
        const jsonContent = JSON.stringify(data, null, 2);
        this.downloadFile(jsonContent, 'data-export.json', 'application/json');
    },
    
    downloadFile(content, filename, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    },
    
    // Cell formatting
    formatCellValue(value, column) {
        if (value === null || value === undefined) return '';
        
        switch (column.type) {
            case 'number':
                const num = parseFloat(value);
                return isNaN(num) ? value : num.toLocaleString();
            
            case 'currency':
                const amount = parseFloat(value);
                return isNaN(amount) ? value : 
                    new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(amount);
            
            case 'date':
                const date = new Date(value);
                return isNaN(date.getTime()) ? value : 
                    date.toLocaleDateString();
            
            case 'datetime':
                const datetime = new Date(value);
                return isNaN(datetime.getTime()) ? value : 
                    datetime.toLocaleString();
            
            case 'boolean':
                return value ? 'Yes' : 'No';
            
            case 'html':
                return value; // Will be rendered as HTML via x-html
            
            default:
                if (column.format && typeof column.format === 'function') {
                    return column.format(value);
                }
                return String(value);
        }
    },
    
    // Responsive behavior
    setupResponsive() {
        this.resizeObserver = new ResizeObserver(() => {
            this.handleResize();
        });
        
        this.resizeObserver.observe(this.$el);
    },
    
    handleResize() {
        const container = this.$el.querySelector('.ds-table-container');
        if (!container) return;
        
        const containerWidth = container.clientWidth;
        const tableWidth = container.querySelector('.ds-table')?.scrollWidth || 0;
        
        // Add/remove horizontal scroll class
        if (tableWidth > containerWidth) {
            container.classList.add('ds-table-container--scrollable');
        } else {
            container.classList.remove('ds-table-container--scrollable');
        }
    },
    
    // Virtualization (basic implementation)
    setupVirtualization() {
        if (!this.virtualized) return;
        
        const container = this.$el.querySelector('.ds-table-container');
        if (!container) return;
        
        container.addEventListener('scroll', () => {
            this.handleVirtualScroll();
        });
        
        this.updateVirtualItems();
    },
    
    handleVirtualScroll() {
        const container = this.$el.querySelector('.ds-table-container');
        if (!container) return;
        
        this.virtualScrollTop = container.scrollTop;
        this.updateVirtualItems();
    },
    
    updateVirtualItems() {
        const containerHeight = 400; // Default height
        const visibleCount = Math.ceil(containerHeight / this.rowHeight);
        const startIndex = Math.floor(this.virtualScrollTop / this.rowHeight);
        
        this.visibleStartIndex = Math.max(0, startIndex - 5); // Buffer
        this.visibleEndIndex = Math.min(
            this.filteredData.length,
            startIndex + visibleCount + 5
        );
        
        // Update paginated data for virtual scrolling
        this.paginatedData = this.filteredData.slice(
            this.visibleStartIndex,
            this.visibleEndIndex
        );
    },
    
    // Public API
    refresh() {
        this.applyFilters();
        this.updatePagination();
    },
    
    addRow(rowData) {
        const newRow = {
            id: rowData.id || Date.now(),
            data: rowData,
            selected: false,
            expanded: false,
            disabled: rowData.disabled || false,
            variant: rowData.variant || null
        };
        
        this.originalData.push(newRow);
        this.refresh();
        
        this.$dispatch('ds-table-row-added', { row: newRow });
        return newRow.id;
    },
    
    removeRow(rowId) {
        const index = this.originalData.findIndex(row => row.id === rowId);
        if (index === -1) return false;
        
        const removedRow = this.originalData.splice(index, 1)[0];
        this.selectedRows = this.selectedRows.filter(id => id !== rowId);
        this.refresh();
        
        this.$dispatch('ds-table-row-removed', { row: removedRow });
        return true;
    },
    
    updateRow(rowId, newData) {
        const row = this.originalData.find(row => row.id === rowId);
        if (!row) return false;
        
        Object.assign(row.data, newData);
        this.refresh();
        
        this.$dispatch('ds-table-row-updated', { row, newData });
        return true;
    },
    
    getSelectedData() {
        return this.filteredData
            .filter(row => row.selected)
            .map(row => row.data);
    }
});