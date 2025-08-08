<?php
/**
 * Dashboard Template
 * 
 * High-performance template for the GoHighLevel-style dashboard
 * Uses AJAX loading for better performance with Supabase/n8n integrations
 * 
 * @package Dashboard
 * @since 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get dashboard section
$dashboard_section = get_query_var('dashboard_section', 'overview');
$user_id = get_current_user_id();

get_header(); ?>

<div id="dashboard-app" class="dashboard-wrapper" data-user-id="<?php echo esc_attr($user_id); ?>" data-section="<?php echo esc_attr($dashboard_section); ?>">
    
    <!-- Loading State -->
    <div id="dashboard-loading" class="dashboard-loading">
        <div class="loading-spinner"></div>
        <p>Loading your dashboard...</p>
    </div>
    
    <!-- Dashboard Container (will be populated via AJAX) -->
    <div id="dashboard-container" style="display: none;">
        <!-- Sidebar Navigation -->
        <nav class="dashboard-sidebar">
            <div class="sidebar-brand">
                <h2><?php echo esc_html(get_option('dashboard_brand_name', 'Dashboard')); ?></h2>
                <button class="mobile-toggle" id="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            
            <div class="sidebar-search">
                <input type="text" class="sidebar-search-input" placeholder="Search..." id="dashboard-search">
                <i class="search-icon fas fa-search"></i>
            </div>
            
            <ul class="sidebar-menu" id="sidebar-menu">
                <!-- Menu items will be loaded dynamically -->
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <?php echo get_avatar($user_id, 32); ?>
                    <span class="user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                    <div class="user-actions">
                        <a href="<?php echo wp_logout_url(); ?>" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Area -->
        <div class="dashboard-main-content">
            <!-- Header Navigation -->
            <header class="dashboard-header">
                <nav class="horizontal-nav">
                    <ul class="nav-tabs" id="horizontal-nav">
                        <li class="nav-tab active">
                            <a href="#" class="nav-tab-link" data-tab="overview">
                                Overview
                                <span class="nav-badge new">3</span>
                            </a>
                        </li>
                        <li class="nav-tab">
                            <a href="#" class="nav-tab-link" data-tab="contacts">
                                Contacts
                                <span class="nav-badge">247</span>
                            </a>
                        </li>
                        <li class="nav-tab">
                            <a href="#" class="nav-tab-link" data-tab="conversations">
                                Conversations
                                <span class="nav-badge warning">5</span>
                            </a>
                        </li>
                        <li class="nav-tab">
                            <a href="#" class="nav-tab-link" data-tab="automation">
                                Automation
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <button class="btn btn-primary" id="canvas-edit-btn">
                        <i class="fas fa-edit"></i> Customize
                    </button>
                    <div class="header-notifications">
                        <button class="notification-bell" id="notification-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-count" id="notification-count" style="display: none;">0</span>
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="dashboard-content-area" id="dashboard-content">
                <!-- Default Overview Content -->
                <div class="dashboard-default-content">
                    <!-- Statistics Cards -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-label">Total Contacts</div>
                            <div class="stat-value"><?php echo number_format(rand(1000, 9999)); ?></div>
                            <div class="stat-change positive">+12.5%</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Active Campaigns</div>
                            <div class="stat-value"><?php echo rand(5, 25); ?></div>
                            <div class="stat-change positive">+3</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Revenue This Month</div>
                            <div class="stat-value">RM<?php echo number_format(rand(10000, 99999)); ?></div>
                            <div class="stat-change positive">+8.3%</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Conversion Rate</div>
                            <div class="stat-value"><?php echo number_format(rand(100, 999) / 100, 1); ?>%</div>
                            <div class="stat-change negative">-0.5%</div>
                        </div>
                    </div>
                    
                    <!-- Dashboard Widgets -->
                    <div class="dashboard-widgets-grid">
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h3>Recent Activity</h3>
                                <a href="#" class="widget-action">View All</a>
                            </div>
                            <div class="widget-content">
                                <div class="activity-item">
                                    <i class="fas fa-user-plus"></i>
                                    <span>New contact added</span>
                                    <time>2 minutes ago</time>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email campaign sent</span>
                                    <time>1 hour ago</time>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>SMS workflow triggered</span>
                                    <time>3 hours ago</time>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Report generated</span>
                                    <time>5 hours ago</time>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h3>Performance Overview</h3>
                                <select class="widget-filter">
                                    <option>Last 30 days</option>
                                    <option>Last 7 days</option>
                                    <option>Yesterday</option>
                                </select>
                            </div>
                            <div class="widget-content">
                                <div class="performance-metrics">
                                    <div class="metric">
                                        <span class="metric-label">Open Rate</span>
                                        <span class="metric-value">24.8%</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Click Rate</span>
                                        <span class="metric-value">3.2%</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Response Rate</span>
                                        <span class="metric-value">12.1%</span>
                                    </div>
                                </div>
                                <div class="chart-placeholder">
                                    <i class="fas fa-chart-area"></i>
                                    <p>Chart will be rendered here with real data from Supabase</p>
                                    <small>🔄 Connected to n8n workflows for real-time updates</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-widget">
                            <div class="widget-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="widget-content">
                                <div class="quick-actions">
                                    <button class="quick-action-btn">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Contact</span>
                                    </button>
                                    <button class="quick-action-btn">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Send Campaign</span>
                                    </button>
                                    <button class="quick-action-btn">
                                        <i class="fas fa-cog"></i>
                                        <span>Create Workflow</span>
                                    </button>
                                    <button class="quick-action-btn">
                                        <i class="fas fa-file-alt"></i>
                                        <span>View Reports</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dynamic content will replace default content when menu items are clicked -->
                <div class="dashboard-dynamic-content" id="dynamic-content" style="display: none;">
                    <!-- Content loaded via AJAX will appear here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>
    
    <!-- Canvas Builder Modal -->
    <div class="canvas-builder-modal" id="canvas-builder-modal" style="display: none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Customize Dashboard</h3>
                <button class="modal-close" id="canvas-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="canvas-builder-content">
                <!-- Canvas builder will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Real-time Notifications -->
    <div class="notification-center" id="notification-center" style="display: none;">
        <div class="notification-header">
            <h4>Notifications</h4>
            <button class="close-notifications" id="close-notifications">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="notification-list" id="notification-list">
            <!-- Notifications will be populated in real-time -->
        </div>
    </div>
</div>

<!-- Performance-optimized CSS loading -->
<style>
.dashboard-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background: #1a1a1a;
    color: #fff;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #333;
    border-top: 4px solid #4285f4;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.content-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #666;
}

.content-loading .loading-spinner {
    width: 30px;
    height: 30px;
    border-width: 3px;
    margin-bottom: 15px;
}
</style>

<!-- Async JavaScript Loading with Enhanced Error Tracking -->
<script>
// Enhanced error tracking for Dashboard
console.log('🚀 Dashboard Template Loading...');

// Track all errors
window.dashboardErrors = [];

// Note about common browser extension errors
console.log('ℹ️ Note: "runtime.lastError" messages are from browser extensions, not the dashboard');

// Font Awesome loading check - Fixed CSP data URI support
function checkFontAwesome() {
    try {
        const testIcon = document.createElement('i');
        testIcon.className = 'fas fa-home';
        testIcon.style.position = 'absolute';
        testIcon.style.left = '-9999px';
        document.body.appendChild(testIcon);
        
        const computed = window.getComputedStyle(testIcon, ':before');
        const content = computed.getPropertyValue('content');
        
        document.body.removeChild(testIcon);
        
        if (!content || content === 'none') {
            console.error('❌ Font Awesome not loaded properly - Check CSP font-src directive');
            window.dashboardErrors.push('Font Awesome loading failed - CSP may be blocking data URIs');
        } else {
            console.log('✅ Font Awesome loaded successfully - CSP font-src fixed');
        }
        
        // Additional CSP check
        if (document.head.querySelector('link[href*="font-awesome"]')) {
            console.log('✅ Font Awesome stylesheet detected');
        } else {
            console.warn('⚠️ Font Awesome stylesheet not found in DOM');
        }
    } catch (error) {
        console.error('❌ Error checking Font Awesome:', error);
        window.dashboardErrors.push('Font Awesome check failed: ' + error.message);
    }
}

// WordPress AJAX availability check
function checkWordPressAjax() {
    if (typeof dashboardConfig !== 'undefined' && dashboardConfig.ajaxUrl) {
        console.log('✅ WordPress AJAX URL available:', dashboardConfig.ajaxUrl);
    } else {
        console.error('❌ WordPress AJAX configuration missing');
        window.dashboardErrors.push('WordPress AJAX configuration missing');
    }
}

// CSS loading check
function checkDashboardCSS() {
    const dashboardWrapper = document.querySelector('.dashboard-wrapper');
    if (dashboardWrapper) {
        const computed = window.getComputedStyle(dashboardWrapper);
        if (computed.display === 'none' || !computed.backgroundColor) {
            console.error('❌ Dashboard CSS might not be loaded properly');
            window.dashboardErrors.push('Dashboard CSS loading issue detected');
        } else {
            console.log('✅ Dashboard CSS loaded successfully');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard Template DOM Ready...');
    
    // Run all checks
    setTimeout(function() {
        checkFontAwesome();
        checkWordPressAjax();
        checkDashboardCSS();
        
        // Report any accumulated errors
        if (window.dashboardErrors.length > 0) {
            console.error('📋 Dashboard Error Summary:', window.dashboardErrors);
        } else {
            console.log('✅ All dashboard systems checked - no errors detected');
        }
    }, 1000);
    
    // Show dashboard immediately (no AJAX dependency for initial view)
    const loadingEl = document.getElementById('dashboard-loading');
    const containerEl = document.getElementById('dashboard-container');
    
    if (loadingEl && containerEl) {
        loadingEl.style.display = 'none';
        containerEl.style.display = 'block';
        console.log('✅ Dashboard displayed');
    } else {
        console.error('❌ Dashboard loading/container elements not found');
        window.dashboardErrors.push('Dashboard DOM elements missing');
    }
    
    // Initialize dashboard navigation (correct class name)
    if (typeof DashboardNavigation !== 'undefined') {
        try {
            window.dashboardNav = new DashboardNavigation();
            console.log('✅ Dashboard Navigation initialized');
        } catch (error) {
            console.error('❌ Dashboard Navigation initialization failed:', error);
            window.dashboardErrors.push('Navigation init failed: ' + error.message);
        }
    } else {
        console.warn('⚠️ DashboardNavigation class not found, will retry...');
        
        // Retry after a short delay
        setTimeout(function() {
            if (typeof DashboardNavigation !== 'undefined') {
                try {
                    window.dashboardNav = new DashboardNavigation();
                    console.log('✅ Dashboard Navigation initialized (delayed)');
                } catch (error) {
                    console.error('❌ Dashboard Navigation initialization failed (delayed):', error);
                    window.dashboardErrors.push('Navigation delayed init failed: ' + error.message);
                }
            } else {
                console.error('❌ DashboardNavigation still not available after delay');
                window.dashboardErrors.push('DashboardNavigation class never loaded');
            }
        }, 500);
    }
    
    // Add WordPress configuration if available
    if (typeof dashboardConfig !== 'undefined') {
        console.log('✅ Dashboard config loaded:', dashboardConfig);
        // Store config globally for navigation class
        window.dashboardConfig = dashboardConfig;
    } else {
        console.warn('⚠️ Dashboard config not available');
        window.dashboardErrors.push('WordPress dashboard config missing');
    }
});

// Network error tracking
window.addEventListener('load', function() {
    // Check for failed resources
    const resources = performance.getEntriesByType('resource');
    const failedResources = resources.filter(resource => resource.responseEnd === 0);
    
    if (failedResources.length > 0) {
        console.error('❌ Failed to load resources:', failedResources.map(r => r.name));
        failedResources.forEach(resource => {
            window.dashboardErrors.push('Failed to load: ' + resource.name);
        });
    } else {
        console.log('✅ All resources loaded successfully');
    }
});
</script>

<?php
// Assets are enqueued by Dashboard_Page_Manager to avoid double loading
get_footer(); ?> 