/**
 * Dashboard Navigation JavaScript - Version 1.0.6 DEBUG
 * Handles GoHighLevel-style navigation and interactions
 * 
 * v1.0.6 DEBUG VERSION:
 * - FIXED: WP Admin menu interference resolved with selective preventDefault
 * - ADDED: Comprehensive error logging for debugging
 * - ENHANCED: Error tracking and console debugging
 * 
 * STATUS: ✅ Debug Version for Error Detection
 */

// Global error handler for dashboard
window.addEventListener('error', function(e) {
    console.error('🚨 JavaScript Error in Dashboard:', {
        message: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno,
        error: e.error
    });
});

// Promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('🚨 Unhandled Promise Rejection in Dashboard:', e.reason);
});

class DashboardNavigation {
    constructor() {
        try {
            console.log('🚀 DashboardNavigation Constructor Starting...');
            
            this.currentTab = 'overview';
            this.sidebarCollapsed = false;
            this.mobileMenuOpen = false;
            
            // Initialize components with error handling
            this.init();
            
            console.log('✅ DashboardNavigation Constructor Completed');
        } catch (error) {
            console.error('❌ Error in DashboardNavigation Constructor:', error);
            throw error;
        }
    }
    
    init() {
        try {
            console.log('🚀 Initializing Dashboard Navigation...');
            
            // Get DOM elements with error checking
            this.sidebar = document.querySelector('.dashboard-sidebar');
            this.mainContent = document.querySelector('.dashboard-main-content');
            this.dashboardWrapper = document.querySelector('.dashboard-wrapper');
            this.mobileOverlay = document.querySelector('.mobile-overlay');
            
            if (!this.sidebar) {
                console.error('❌ Sidebar element not found');
                throw new Error('Dashboard sidebar element not found');
            }
            if (!this.mainContent) {
                console.error('❌ Main content element not found');
                throw new Error('Dashboard main content element not found');
            }
            if (!this.dashboardWrapper) {
                console.error('❌ Dashboard wrapper element not found');
                throw new Error('Dashboard wrapper element not found');
            }
            
            console.log('✅ DOM elements found successfully');
            
            // Initialize mobile collapsed state
            if (window.innerWidth <= 1024) {
                this.sidebarCollapsed = true;
                this.sidebar.classList.add('collapsed');
                this.dashboardWrapper.classList.add('sidebar-collapsed');
                console.log('📱 Mobile collapsed state activated');
            }
            
            // Create UI components with error handling
            this.createSidebarToggle();
            this.createMobileMenuToggle();
            this.createMenuSections();
            
            // Bind events with error handling
            this.bindEvents();
            
            // Show initial tab
            this.showVerticalMenuSection(this.currentTab);
            
            console.log('🎉 Dashboard Navigation initialized successfully!');
        } catch (error) {
            console.error('❌ Error in Dashboard Navigation init():', error);
            throw error;
        }
    }
    
    createSidebarToggle() {
        const sidebarBrand = this.sidebar.querySelector('.sidebar-brand');
        if (!sidebarBrand) return;
        
        // Remove existing toggle
        const existingToggle = sidebarBrand.querySelector('.sidebar-toggle');
        if (existingToggle) existingToggle.remove();
        
        const toggleButton = document.createElement('button');
        toggleButton.className = 'sidebar-toggle';
        toggleButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle Sidebar');
        toggleButton.type = 'button';
        
        sidebarBrand.appendChild(toggleButton);
        console.log('✅ Sidebar toggle button created');
    }
    
    createMobileMenuToggle() {
        const headerActions = document.querySelector('.header-actions');
        if (!headerActions) return;
        
        // Remove existing toggle
        const existingToggle = headerActions.querySelector('.mobile-menu-toggle');
        if (existingToggle) existingToggle.remove();
        
        const toggleButton = document.createElement('button');
        toggleButton.className = 'mobile-menu-toggle';
        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle Mobile Menu');
        toggleButton.type = 'button';
        
        headerActions.insertBefore(toggleButton, headerActions.firstChild);
        console.log('✅ Mobile menu toggle created');
    }
    
    createMenuSections() {
        const menuSections = {
            'overview': {
                title: 'Overview',
                items: [
                    { name: 'Dashboard', icon: 'fas fa-tachometer-alt', page: 'dashboard' },
                    { name: 'Statistics', icon: 'fas fa-chart-line', page: 'stats' },
                    { name: 'Recent Activity', icon: 'fas fa-clock', page: 'activity' },
                    { name: 'Reports', icon: 'fas fa-file-alt', page: 'reports' }
                ]
            },
            'contacts': {
                title: 'Contacts',
                items: [
                    { name: 'All Contacts', icon: 'fas fa-users', page: 'contacts' },
                    { name: 'Contact Groups', icon: 'fas fa-layer-group', page: 'contact-groups' },
                    { name: 'Import/Export', icon: 'fas fa-exchange-alt', page: 'import-export' },
                    { name: 'Custom Fields', icon: 'fas fa-cogs', page: 'custom-fields' }
                ]
            },
            'conversations': {
                title: 'Conversations',
                items: [
                    { name: 'All Messages', icon: 'fas fa-comments', page: 'messages' },
                    { name: 'Email', icon: 'fas fa-envelope', page: 'email' },
                    { name: 'SMS', icon: 'fas fa-sms', page: 'sms' },
                    { name: 'Chat Widget', icon: 'fas fa-comment-dots', page: 'chat' }
                ]
            },
            'automation': {
                title: 'Automation',
                items: [
                    { name: 'Workflows', icon: 'fas fa-project-diagram', page: 'workflows' },
                    { name: 'Campaigns', icon: 'fas fa-bullhorn', page: 'campaigns' },
                    { name: 'Templates', icon: 'fas fa-file-alt', page: 'templates' },
                    { name: 'Triggers', icon: 'fas fa-bolt', page: 'triggers' }
                ]
            }
        };
        
        const sidebarMenu = this.sidebar.querySelector('.sidebar-menu');
        if (!sidebarMenu) return;
        
        sidebarMenu.innerHTML = '';
        
        Object.keys(menuSections).forEach(sectionKey => {
            const section = menuSections[sectionKey];
            const sectionElement = document.createElement('div');
            sectionElement.className = `sidebar-menu-section`;
            sectionElement.dataset.section = sectionKey;
            
            if (sectionKey === this.currentTab) {
                sectionElement.classList.add('active');
            }
            
            let sectionHTML = '';
            section.items.forEach((item, index) => {
                const isActive = index === 0 && sectionKey === this.currentTab ? 'active' : '';
                sectionHTML += `
                    <li class="menu-item ${isActive}">
                        <a href="#" class="menu-link" data-page="${item.page}" data-tooltip="${item.name}">
                            <i class="menu-icon ${item.icon}"></i>
                            <span class="menu-text">${item.name}</span>
                        </a>
                    </li>
                `;
            });
            
            sectionElement.innerHTML = `<ul style="list-style: none;">${sectionHTML}</ul>`;
            sidebarMenu.appendChild(sectionElement);
        });
        
        console.log('✅ Menu sections created');
    }
    
    bindEvents() {
        // Event delegation for all clicks
        document.addEventListener('click', (e) => {
            // Only prevent default for dashboard navigation elements
            // DO NOT prevent default for WordPress admin menu items
            
            // Check if clicking on WordPress admin elements - let them work normally
            if (e.target.closest('#wpadminbar') || 
                e.target.closest('#adminmenu') || 
                e.target.closest('.wp-admin') ||
                e.target.closest('#wpbody') ||
                e.target.closest('#wp-toolbar')) {
                // Let WordPress admin elements function normally
                return;
            }
            
            // Only handle dashboard navigation clicks
            let dashboardElement = false;
            
            // Sidebar menu clicks
            if (e.target.closest('.menu-link')) {
                e.preventDefault();
                this.handleSidebarMenuClick(e.target.closest('.menu-link'));
                dashboardElement = true;
            }
            
            // Horizontal navigation clicks
            if (e.target.closest('.nav-tab-link')) {
                e.preventDefault();
                this.handleHorizontalNavClick(e.target.closest('.nav-tab-link'));
                dashboardElement = true;
            }
            
            // Sidebar toggle clicks
            if (e.target.closest('.sidebar-toggle')) {
                e.preventDefault();
                this.toggleSidebar();
                dashboardElement = true;
            }
            
            // Mobile menu toggle clicks
            if (e.target.closest('.mobile-menu-toggle')) {
                e.preventDefault();
                this.toggleMobileMenu();
                dashboardElement = true;
            }
            
            // Mobile overlay clicks
            if (e.target.classList.contains('mobile-overlay')) {
                e.preventDefault();
                this.closeMobileMenu();
                dashboardElement = true;
            }
            
            // Only prevent default if we handled a dashboard element
            if (!dashboardElement) {
                // Let other links work normally (including WP admin menu)
                return;
            }
        });
        
        console.log('✅ Event listeners bound');
    }
    
    handleHorizontalNavClick(navLink) {
        const tab = navLink.dataset.tab;
        if (!tab) return;
        
        console.log(`🔄 Switching to tab: ${tab}`);
        
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(tabElement => {
            tabElement.classList.remove('active');
        });
        navLink.closest('.nav-tab').classList.add('active');
        
        // Update current tab
        this.currentTab = tab;
        
        // Show corresponding vertical menu section
        this.showVerticalMenuSection(tab);
    }
    
    handleSidebarMenuClick(menuLink) {
        const page = menuLink.dataset.page;
        
        console.log(`📄 Opening page: ${page}`);
        
        // Update active menu item
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        menuLink.closest('.menu-item').classList.add('active');
        
        // Close mobile menu if open
        if (this.mobileMenuOpen) {
            this.closeMobileMenu();
        }
    }
    
    showVerticalMenuSection(tab) {
        console.log(`📋 Showing menu section: ${tab}`);
        
        const sections = this.sidebar.querySelectorAll('.sidebar-menu-section');
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        const targetSection = this.sidebar.querySelector(`[data-section="${tab}"]`);
        if (targetSection) {
            targetSection.classList.add('active');
            console.log(`✅ Menu section ${tab} activated`);
        } else {
            console.warn(`⚠️ Menu section ${tab} not found`);
        }
    }
    
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        
        console.log(`🔧 Sidebar ${this.sidebarCollapsed ? 'collapsed' : 'expanded'}`);
        
        if (this.sidebarCollapsed) {
            this.sidebar.classList.add('collapsed');
            this.dashboardWrapper.classList.add('sidebar-collapsed');
            const icon = this.sidebar.querySelector('.sidebar-toggle i');
            if (icon) icon.className = 'fas fa-chevron-right';
            

            
        } else {
            this.sidebar.classList.remove('collapsed');
            this.dashboardWrapper.classList.remove('sidebar-collapsed');
            const icon = this.sidebar.querySelector('.sidebar-toggle i');
            if (icon) icon.className = 'fas fa-chevron-left';
        }
    }
    
    toggleMobileMenu() {
        // On mobile, always use collapsed sidebar instead of overlay
        if (window.innerWidth <= 1024) {
            this.toggleSidebar();
            return;
        }
        
        this.mobileMenuOpen = !this.mobileMenuOpen;
        
        if (this.mobileMenuOpen) {
            this.openMobileMenu();
        } else {
            this.closeMobileMenu();
        }
    }
    
    openMobileMenu() {
        // On mobile, expand to full sidebar
        if (window.innerWidth <= 1024) {
            this.sidebarCollapsed = false;
            this.sidebar.classList.remove('collapsed');
            this.sidebar.classList.add('open');
            this.dashboardWrapper.classList.remove('sidebar-collapsed');
            return;
        }
        
        this.sidebar.classList.add('open');
        this.mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        console.log('📱 Mobile menu opened');
    }
    
    closeMobileMenu() {
        // On mobile, return to collapsed sidebar
        if (window.innerWidth <= 1024) {
            this.sidebarCollapsed = true;
            this.sidebar.classList.add('collapsed');
            this.sidebar.classList.remove('open');
            this.dashboardWrapper.classList.add('sidebar-collapsed');
            return;
        }
        
        this.sidebar.classList.remove('open');
        this.mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
        
        console.log('📱 Mobile menu closed');
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof DashboardNavigation !== 'undefined') {
        window.dashboardNavigation = new DashboardNavigation();
        console.log('🎉 Dashboard Navigation Auto-Initialized');
    }
});

// WordPress Integration & Backward Compatibility
window.DashboardApp = {
    init: function(config) {
        console.log('🔄 DashboardApp.init() called (compatibility mode)');
        
        if (window.dashboardNavigation) {
            console.log('✅ Using existing DashboardNavigation instance');
            return window.dashboardNavigation;
        }
        
        if (typeof DashboardNavigation !== 'undefined') {
            window.dashboardNavigation = new DashboardNavigation();
            console.log('✅ Created new DashboardNavigation instance');
            return window.dashboardNavigation;
        }
        
        console.error('❌ DashboardNavigation class not available');
        return null;
    }
};

// WordPress AJAX Integration
if (typeof dashboardConfig !== 'undefined') {
    console.log('✅ WordPress dashboard config loaded');
    
    // Add WordPress-specific functionality here
    window.wpDashboard = {
        config: dashboardConfig,
        
        // Load content via WordPress AJAX
        loadContent: function(page, callback) {
            if (!dashboardConfig.ajaxUrl || !dashboardConfig.nonce) {
                console.error('❌ WordPress AJAX not configured');
                return;
            }
            
            fetch(dashboardConfig.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'dashboard_load_content',
                    page: page,
                    nonce: dashboardConfig.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('❌ AJAX load error:', error);
            });
        }
    };
}

console.log('🚀 Dashboard Navigation System Ready!'); 