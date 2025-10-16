@extends('filament::layouts.app')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.fi-sidebar');
        const content = document.querySelector('.fi-main');
        const expandIcon = document.querySelector('.sidebar-expand');
        const collapseIcon = document.querySelector('.sidebar-collapse');
        
        if (sidebarToggle && sidebar && content) {
            // Check for saved state
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                content.classList.add('expanded');
                collapseIcon.classList.add('hidden');
                expandIcon.classList.remove('hidden');
            }

            window.addEventListener('collapse-sidebar', function() {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
                
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                const sidebarHeader = document.querySelector('.fi-sidebar-header');
                
                if (isCollapsed) {
                    collapseIcon.classList.add('hidden');
                    expandIcon.classList.remove('hidden');
                    if (sidebarHeader) {
                        sidebarHeader.style.display = 'none';
                        sidebarHeader.style.visibility = 'hidden';
                    }
                } else {
                    collapseIcon.classList.remove('hidden');
                    expandIcon.classList.add('hidden');
                    if (sidebarHeader) {
                        sidebarHeader.style.display = '';
                        sidebarHeader.style.visibility = '';
                    }
                }
                
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }
    });
</script>
@endpush

@push('styles')
<style>
    .fi-sidebar {
        transition: width 0.3s ease;
    }
    
    .fi-sidebar.collapsed {
        width: auto;
        min-width: auto;
        overflow: visible;
    }
    
    /* Hide sidebar content except header when collapsed */
    .fi-sidebar.collapsed .fi-sidebar-nav,
    .fi-sidebar.collapsed .fi-sidebar-group,
    .fi-sidebar.collapsed .fi-sidebar-item:not(.fi-sidebar-header) {
        display: none;
    }
    
    /* Hide header when collapsed */
    .fi-sidebar.collapsed .fi-sidebar-header {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
        position: absolute !important;
        z-index: -1 !important;
    }
    
    .fi-main {
        transition: margin-left 0.3s ease, width 0.3s ease, max-width 0.3s ease;
    }
    
    .fi-main.expanded {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    /* Force content to full width in expanded mode */
    .fi-main.expanded .fi-page {
        max-width: 100% !important;
        padding-left: 2rem !important;
        padding-right: 2rem !important;
    }
    
    /* Add space to brand name */
    .fi-sidebar-header {
        padding-left: 2.5rem !important;
    }
    
    /* Sidebar toggle container styles */
    .sidebar-toggle-container {
        position: fixed;
        left: 0;
        top: 0.85rem;
        z-index: 50;
        background-color: transparent;
        transition: left 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .fi-sidebar.collapsed + div .sidebar-toggle-container {
        left: 0;
    }
    
    /* Hide toggle when sidebar is collapsed */
    .collapsed-toggle {
        display: none !important;
    }
    
    #sidebar-toggle {
        border-radius: 50%;
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    .dark #sidebar-toggle {
        background-color: var(--gray-800);
    }
</style>
@endpush 