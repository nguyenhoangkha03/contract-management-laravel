<div class="sidebar-toggle-container flex items-center" 
    x-data="{}"
    x-bind:class="{ 'collapsed-toggle': $store.sidebar.isOpen === false }">
    <button
        id="sidebar-toggle"
        x-data="{}"
        x-on:click="$dispatch('collapse-sidebar')"
        type="button"
        class="flex h-10 w-10 items-center justify-center text-primary-500 transition hover:bg-gray-500/5 dark:hover:bg-gray-700">
        <!-- Collapse arrow icon (shown when sidebar is expanded) -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="sidebar-collapse w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
        </svg>
        <!-- Expand arrow icon (shown when sidebar is collapsed) -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="sidebar-expand hidden w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</div> 