{{-- Tabs Component with lazy loading and dynamic content --}}

<div {{ $attributes->merge($getComponentAttributes()) }}>
    {{-- Tab Navigation --}}
    <div class="{{ $getTabNavClasses() }}" 
         id="{{ $getTabNavId() }}"
         role="tablist"
         :aria-orientation="orientation">
        
        {{-- Scrollable wrapper for many tabs --}}
        <div class="ds-tabs-nav-wrapper" 
             :class="{'ds-tabs-nav-wrapper--scrollable': scrollable}">
            
            {{-- Tab buttons --}}
            <template x-for="(tab, index) in tabs" :key="tab.id">
                <button type="button"
                        class="ds-tab-button"
                        :class="{
                            'ds-tab-button--active': activeTab === tab.id,
                            'ds-tab-button--disabled': tab.disabled
                        }"
                        :id="'tab-' + tab.id"
                        :aria-controls="'panel-' + tab.id"
                        :aria-selected="activeTab === tab.id"
                        :tabindex="activeTab === tab.id ? '0' : '-1'"
                        :disabled="tab.disabled"
                        role="tab"
                        x-on:click="!tab.disabled && selectTab(tab.id)"
                        x-on:keydown="handleTabKeydown($event, tab.id)">
                    
                    {{-- Tab icon --}}
                    <span class="ds-tab-icon" x-show="tab.icon" x-html="tab.icon"></span>
                    
                    {{-- Tab label --}}
                    <span class="ds-tab-label" x-text="tab.label"></span>
                    
                    {{-- Tab badge --}}
                    <span class="ds-tab-badge" 
                          x-show="tab.badge" 
                          x-text="tab.badge"></span>
                    
                    {{-- Close button --}}
                    <button type="button"
                            class="ds-tab-close"
                            x-show="tab.closable && tabs.length > 1"
                            x-on:click.stop="closeTab(tab.id)"
                            :aria-label="'Close ' + tab.label + ' tab'"
                            tabindex="-1">
                        <svg viewBox="0 0 16 16" fill="currentColor">
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </button>
                </button>
            </template>
            
            {{-- Add new tab button --}}
            <button type="button"
                    class="ds-tab-add"
                    x-show="addable"
                    x-on:click="addTab()"
                    aria-label="Add new tab">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
            </button>
        </div>
        
        {{-- Scroll buttons for scrollable tabs --}}
        <div class="ds-tabs-scroll-controls" x-show="scrollable && hasOverflow">
            <button type="button" 
                    class="ds-tabs-scroll-button ds-tabs-scroll-button--left"
                    x-on:click="scrollTabs('left')"
                    :disabled="!canScrollLeft"
                    aria-label="Scroll tabs left">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
            </button>
            <button type="button" 
                    class="ds-tabs-scroll-button ds-tabs-scroll-button--right"
                    x-on:click="scrollTabs('right')"
                    :disabled="!canScrollRight"
                    aria-label="Scroll tabs right">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
    </div>
    
    {{-- Tab Content Panels --}}
    <div class="{{ $getTabContentClasses() }}" 
         id="{{ $getTabContentId() }}">
        
        <template x-for="tab in tabs" :key="tab.id">
            <div class="ds-tab-panel"
                 :class="{
                     'ds-tab-panel--active': activeTab === tab.id,
                     'ds-tab-panel--loading': tab.loading
                 }"
                 :id="'panel-' + tab.id"
                 :aria-labelledby="'tab-' + tab.id"
                 :hidden="activeTab !== tab.id"
                 role="tabpanel"
                 tabindex="0">
                
                {{-- Static content --}}
                <div class="ds-tab-content" 
                     x-show="!tab.lazy || tab.loaded"
                     x-html="tab.content"></div>
                
                {{-- Lazy loading placeholder --}}
                <div class="ds-tab-lazy-placeholder" 
                     x-show="tab.lazy && !tab.loaded && !tab.loading">
                    <button type="button" 
                            class="ds-tab-load-button"
                            x-on:click="loadTabContent(tab.id)">
                        Load Content
                    </button>
                </div>
                
                {{-- Loading state --}}
                <div class="ds-tab-loading" x-show="tab.loading">
                    <div class="ds-loading-spinner">
                        <div class="ds-spinner"></div>
                    </div>
                    <div class="ds-loading-message">Loading tab content...</div>
                </div>
                
                {{-- Error state --}}
                <div class="ds-tab-error" x-show="tab.error">
                    <div class="ds-error-icon">⚠️</div>
                    <div class="ds-error-message" x-text="tab.errorMessage"></div>
                    <button type="button" 
                            class="ds-tab-retry-button"
                            x-on:click="retryTabContent(tab.id)">
                        Retry
                    </button>
                </div>
            </div>
        </template>
        
        {{-- Default slot content (when no tabs defined) --}}
        @if(empty($tabs))
            <div class="ds-tab-default-content">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>