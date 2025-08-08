<?php
/**
 * Dashboard Plugin - Admin Menu Manager v1.1.3
 * 
 * Handles all WordPress admin menu functionality for CPaaS Dashboard
 * Provides centralized admin interface for dashboard customizations
 * 
 * @package Dashboard
 * @since 1.1.7
 */

namespace Dashboard\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Dashboard_Admin_Menu {
    
    /**
     * Plugin version
     */
    private $version;
    
    /**
     * Security manager instance
     */
    private $security;
    
    /**
     * Page manager instance  
     */
    private $page_manager;
    
    /**
     * Class constructor for proper initialization
     *
     * @since 1.1.0
     * @since 1.1.3 Enhanced with table creation and improved hooks
     */
    public function __construct($version = '1.1.3') {
        $this->version = $version;
        
        // Create the settings table if it doesn't exist
        add_action('admin_init', array($this, 'create_settings_table'));
        
        // Register admin menu
        add_action('admin_menu', array($this, 'add_settings_page'));
        
        // Register settings form handler
        add_action('admin_post_save_dashboard_settings', array($this, 'handle_settings_form'));
        
        // Register AJAX handlers for settings (future expansion)
        add_action('wp_ajax_get_dashboard_setting', array($this, 'ajax_get_setting'));
        add_action('wp_ajax_update_dashboard_setting', array($this, 'ajax_update_setting'));
        
        // Handle database upgrade if needed
        add_action('plugins_loaded', array($this, 'maybe_upgrade_database'));
        
        $this->init_hooks();
    }
    
    /**
     * Set dependencies
     */
    public function set_dependencies($security, $page_manager) {
        $this->security = $security;
        $this->page_manager = $page_manager;
    }
    
    /**
     * Initialize hooks for admin functionality
     *
     * @since 1.1.0
     * @since 1.1.3 Enhanced with security and performance improvements
     */
    public function init_hooks() {
        // Remove login redirect hooks (moved to constructor for better organization)
        
        // Improved hook for dashboard access
        add_action('template_redirect', array($this, 'check_dashboard_access'));
        
        // Login redirect filter with security context
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 999, 3);
        
        // Handle PMPro integration if available
        if (function_exists('pmpro_getOption')) {
            add_filter('pmpro_login_redirect_url', array($this, 'pmpro_override_redirect'), 20, 3);
        }
        
        // Load admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Disable heartbeat on our admin pages for performance
        add_action('admin_init', array($this, 'disable_heartbeat_on_our_pages'));
        
        // Add a settings link on the plugins page
        add_filter('plugin_action_links_dashboard/dashboard-main.php', array($this, 'add_settings_link'));
        
        // Add URL-based cache clearing utility
        add_action('init', array($this, 'handle_cache_clear_request'));
    }
    
    /**
     * Disable WordPress Heartbeat API on our admin pages
     * 
     * @since 1.1.3
     */
    public function disable_heartbeat_on_our_pages() {
        global $pagenow;
        
        // Check if we're on one of our admin pages
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        
        $our_pages = [
            'dashboard-settings',
            'dashboard-diagnostics', 
            'cpaas-settings',
            'cpaas-diagnostics',
            'cpaas-dashboard',
            'cpaas-login-redirects',
            'cpaas-dashboard-settings',
            'cpaas-containers'
        ];
        
        if ($pagenow === 'admin.php' && in_array($current_page, $our_pages)) {
            // Disable heartbeat completely on our pages
            wp_deregister_script('heartbeat');
            
            // Also disable auto-save and post locks which use heartbeat
            wp_dequeue_script('autosave');
            wp_dequeue_script('wp-auth-check');
            
            // Add custom script to prevent heartbeat initialization
            add_action('admin_footer', function() {
                echo '<script>
                    if (typeof wp !== "undefined" && wp.heartbeat) {
                        wp.heartbeat.stop();
                    }
                    if (typeof window.heartbeat !== "undefined") {
                        clearInterval(window.heartbeat);
                    }
                </script>';
            });
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @since 1.1.3
     * @param string $hook Current admin page
     */
    public function enqueue_admin_scripts($hook) {
        // Removed excessive admin script hook logging to reduce noise
        
        // Only load on our settings pages - expanded to include all our admin pages
        if (strpos($hook, 'dashboard-') !== false || 
            strpos($hook, 'cpaas-') !== false || 
            strpos($hook, 'dashboard_page_') !== false ||
            strpos($hook, 'toplevel_page_cpaas-dashboard') !== false ||
            strpos($hook, 'toplevel_page_cpaas-settings') !== false) {
            
            // Disable WordPress Heartbeat API on our admin pages to improve performance
            // The heartbeat polling every 60 seconds is resource-intensive and not needed here
            wp_deregister_script('heartbeat');
            
            // Enqueue admin styles
            wp_enqueue_style(
                'dashboard-admin-styles',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/dashboard-admin.css',
                array(),
                $this->version
            );
            
            // Enqueue admin scripts
            wp_enqueue_script(
                'dashboard-admin-scripts',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/dashboard-admin.js',
                array('jquery'),
                $this->version,
                true
            );
            
            // Localize script with nonce and ajax url
            wp_localize_script('dashboard-admin-scripts', 'dashboardAdmin', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dashboard_ajax_nonce'),
                'diagnosticsUrl' => admin_url('admin.php?page=cpaas-diagnostics')
            ));
        }
    }
    
    /**
     * Add settings link to plugin listing
     *
     * @since 1.1.3
     * @since 1.1.4 Updated for new CPaaS menu structure
     * @param array $links Array of plugin action links
     * @return array Modified array of plugin action links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=cpaas-settings') . '">' . __('Settings', 'dashboard-plugin') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Render the settings page with enhanced security and user feedback
     *
     * @since 1.1.0
     * @since 1.1.3 Enhanced with better feedback and diagnostic tools
     */
    public function render_settings_page() {
        // Security check
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dashboard-plugin'));
        }
        
        // Get current settings with fallbacks
        $redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        $redirect_target = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        $expired_redirect_target = $this->get_db_setting('expired_member_redirect_target', '/membership-account/');
        
        // Display settings updated message if applicable
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'dashboard-plugin') . '</p></div>';
        }
        
        // Display cache cleared message if applicable
        if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] == 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('All caches and transients cleared successfully!', 'dashboard-plugin') . '</p></div>';
        }
        
        // Check if table exists and display warning if not
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            echo '<div class="notice notice-error"><p>' . 
                __('Settings table not found! Please deactivate and reactivate the plugin to fix this issue.', 'dashboard-plugin') .
                ' <a href="' . admin_url('admin.php?page=cpaas-diagnostics') . '">' . __('Run Diagnostics', 'dashboard-plugin') . '</a>' .
                '</p></div>';
        }
        
        // Display settings form with nonce field
        ?>
        <div class="wrap">
            <h1><?php _e('CPaaS Login Redirects', 'dashboard-plugin'); ?></h1>
            
            <div class="notice notice-info">
                <h3>How Login Redirects Work:</h3>
                <p><strong>Scenario 1:</strong> Active PMPro members → Redirect to dashboard URL (Field 1)</p>
                <p><strong>Scenario 2:</strong> Expired/Admin-cancelled PMPro members + Non-PMPro users → Redirect to custom URL (Field 2)</p>
            </div>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="save_dashboard_settings">
                <?php wp_nonce_field('save_dashboard_settings', 'dashboard_settings_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Enable Custom Login Redirects', 'dashboard-plugin'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Enable Login Redirect', 'dashboard-plugin'); ?></span></legend>
                                <label for="dashboard_redirect_after_login">
                                    <input name="dashboard_redirect_after_login" type="checkbox" id="dashboard_redirect_after_login" value="1" <?php checked($redirect_enabled, 1); ?>>
                                    <?php _e('Enable custom login redirects (overrides PMPro default behavior)', 'dashboard-plugin'); ?>
                                </label>
                                <p class="description">When enabled, users will be redirected based on their PMPro membership status instead of the default PMPro account page.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="dashboard_redirect_target"><?php _e('Active PMPro Members → Dashboard URL', 'dashboard-plugin'); ?></label></th>
                        <td>
                            <input name="dashboard_redirect_target" type="text" id="dashboard_redirect_target" value="<?php echo esc_attr($redirect_target); ?>" class="regular-text">
                            <p class="description"><?php _e('Where active PMPro members go after login (e.g. /dashboard/ or /membership-levels/)', 'dashboard-plugin'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Enable Non-Active Member Redirect', 'dashboard-plugin'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Enable Non-Active Member Redirect', 'dashboard-plugin'); ?></span></legend>
                                <label for="expired_member_redirect_after_login">
                                    <input name="expired_member_redirect_after_login" type="checkbox" id="expired_member_redirect_after_login" value="1" <?php checked($expired_redirect_enabled, 1); ?>>
                                    <?php _e('Redirect expired/cancelled PMPro members and non-PMPro users to custom URL', 'dashboard-plugin'); ?>
                                </label>
                                <p class="description">When enabled, users who are NOT active PMPro members will be redirected to your custom URL instead of the default.</p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="expired_member_redirect_target"><?php _e('Non-Active Members → Custom URL', 'dashboard-plugin'); ?></label></th>
                        <td>
                            <input name="expired_member_redirect_target" type="text" id="expired_member_redirect_target" value="<?php echo esc_attr($expired_redirect_target); ?>" class="regular-text">
                            <p class="description"><?php _e('Where expired/cancelled PMPro members and non-PMPro users go after login (e.g. /membership-account/ or /signup/)', 'dashboard-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'dashboard-plugin'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=cpaas-diagnostics'); ?>" class="button button-secondary"><?php _e('Diagnostics', 'dashboard-plugin'); ?></a>
                    <a href="<?php echo add_query_arg('clear_cache', 'true', $_SERVER['REQUEST_URI']); ?>" class="button button-secondary" onclick="return confirm('Are you sure you want to clear all caches and transients?')"><?php _e('Clear All Caches', 'dashboard-plugin'); ?></a>
                </p>
            </form>
            
            <div class="notice notice-warning" style="margin-top: 20px;">
                <h3>Testing Instructions:</h3>
                <p><strong>If changes don't appear immediately:</strong> Click "Clear All Caches" button above to clear WordPress and plugin caches.</p>
                <p><strong>Manual Cache Clear URL:</strong> <code><?php echo add_query_arg('clear_cache', 'true', home_url()); ?></code></p>
                <p><strong>Debug Mode:</strong> Check debug.log for detailed redirect behavior logging.</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Register admin menu and submenus
     */
    /*
    // COMMENTED OUT: Future expansion - Complex admin menu system
    // Currently using simple add_settings_page() method instead
    public function register_admin_menu() {
        // Main CPaaS Dashboard menu
        $main_menu = add_menu_page(
            'CPaaS Dashboard',                          // Page title
            'CPaaS Dashboard',                          // Menu title
            'manage_options',                           // Capability
            'cpaas-dashboard',                          // Menu slug
            [$this, 'render_dashboard_overview'],       // Callback
            'dashicons-dashboard',                      // Icon
            30                                          // Position
        );
        
        // Overview submenu (duplicate of main menu)
        add_submenu_page(
            'cpaas-dashboard',                          // Parent slug
            'Dashboard Overview',                       // Page title
            'Overview',                                 // Menu title
            'manage_options',                           // Capability
            'cpaas-dashboard',                          // Menu slug (same as parent)
            [$this, 'render_dashboard_overview']        // Callback
        );
        
        // Login Redirects submenu
        add_submenu_page(
            'cpaas-dashboard',                          // Parent slug
            'Login Redirects',                          // Page title
            'Login Redirects',                          // Menu title
            'manage_options',                           // Capability
            'cpaas-login-redirects',                    // Menu slug
            [$this, 'render_login_redirects']           // Callback
        );
        
        // Dashboard Settings submenu
        add_submenu_page(
            'cpaas-dashboard',                          // Parent slug
            'Dashboard Settings',                       // Page title
            'Settings',                                 // Menu title
            'manage_options',                           // Capability
            'cpaas-dashboard-settings',                 // Menu slug
            [$this, 'render_dashboard_settings']        // Callback
        );
        
        // Container Management submenu (for future Phase 3)
        add_submenu_page(
            'cpaas-dashboard',                          // Parent slug
            'Container Management',                     // Page title
            'Containers',                               // Menu title
            'manage_options',                           // Capability
            'cpaas-containers',                         // Menu slug
            [$this, 'render_container_management']      // Callback
        );
        
        // Add hooks for admin page loads
        add_action('load-' . $main_menu, [$this, 'handle_admin_actions']);
    }
    */
    
    /**
     * Register settings
     */
    /*
    // COMMENTED OUT: Future expansion - WordPress Settings API
    // Currently using custom database table method instead
    public function register_settings() {
        // Login Redirect Settings Section
        add_settings_section(
            'cpaas_login_redirect_section',
            'Login Redirect Configuration',
            [$this, 'render_login_redirect_section'],
            'cpaas-login-redirects'
        );
        
        // Dashboard redirect after login setting
        register_setting(
            'cpaas_login_redirects',
            'dashboard_redirect_after_login',
            [
                'type' => 'boolean',
                'description' => 'Redirect PMPro members to dashboard after login',
                'default' => 1,
                'sanitize_callback' => 'absint'
            ]
        );
        
        // Redirect target setting
        register_setting(
            'cpaas_login_redirects',
            'dashboard_redirect_target',
            [
                'type' => 'string',
                'description' => 'Target URL for dashboard redirects',
                'default' => '/dashboard/',
                'sanitize_callback' => 'sanitize_text_field'
            ]
        );
    }
    */
    
    /**
     * Enqueue admin assets
     */
    /*
    // COMMENTED OUT: Future expansion - Alternative asset loading
    // Currently using enqueue_admin_scripts() method instead
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        $our_pages = [
            'toplevel_page_cpaas-dashboard',
            'cpaas-dashboard_page_cpaas-login-redirects',
            'cpaas-dashboard_page_cpaas-dashboard-settings',
            'cpaas-dashboard_page_cpaas-containers'
        ];
        
        if (!in_array($hook, $our_pages)) {
            return;
        }
        
        // Disable WordPress Heartbeat API on our admin pages to improve performance
        // The heartbeat polling every 60 seconds is resource-intensive and not needed here
        wp_deregister_script('heartbeat');
        
        // Enqueue admin styles
        wp_enqueue_style(
            'cpaas-dashboard-admin',
            DASHBOARD_PLUGIN_URL . 'assets/css/admin-dashboard.css',
            [],
            $this->version
        );
        
        // Enqueue admin scripts
        wp_enqueue_script(
            'cpaas-dashboard-admin',
            DASHBOARD_PLUGIN_URL . 'assets/js/admin-dashboard.js',
            ['jquery'],
            $this->version,
            true
        );
        
        // Localize script with security nonce and other data since 1.1.2
        wp_localize_script(
            'cpaas-dashboard-admin',
            'cpaasAdmin',
            [
                'nonce' => wp_create_nonce('cpaas_admin_nonce'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'currentPage' => $hook,
                'version' => $this->version,
                'debug' => defined('WP_DEBUG') && WP_DEBUG,
            ]
        );
    }
    */
    
    /**
     * Handle admin form submissions since 1.1.3
     */
    /*
    // COMMENTED OUT: Future expansion - Alternative form handling
    // Currently using handle_settings_form() method instead
    public function handle_admin_actions() {
        // Debug the incoming request - uncomment if needed
        // since 1.1.3
        // error_log('POST data in handle_admin_actions: ' . print_r($_POST, true));
        
        // Check if we have a form submission
        if (isset($_POST['submit'])) {
            // Check nonce is set
            if (!isset($_POST['_wpnonce'])) {
                error_log('Nonce field is missing in form submission');
                return;
            }
            
            // Verify nonce
            if (!wp_verify_nonce($_POST['_wpnonce'], 'cpaas_admin_nonce')) {
                error_log('Nonce verification failed');
                return;
            }
            
            // Process the submission
            $this->process_form_submission();
        }
    }
    */
    
    /**
     * Process form submissions
     */
    /*
    // COMMENTED OUT: Future expansion - Alternative form processing
    // Currently using handle_settings_form() method instead
    private function process_form_submission() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Process based on action
        $action = sanitize_text_field($_POST['action'] ?? '');
        
        switch ($action) {
            case 'update_login_redirects':
                $this->update_login_redirect_settings();
                break;
            case 'update_dashboard_settings':
                $this->update_dashboard_general_settings();
                break;
        }
    }
    */
    
    /**
     * Update login redirect settings
     * 
     * @since 1.1.3 Updated to use dedicated settings table
     */
    /*
    // COMMENTED OUT: Future expansion - Alternative settings update
    // Currently using handle_settings_form() method instead
    private function update_login_redirect_settings() {
        error_log('Form submission received in update_login_redirect_settings: ' . print_r($_POST, true));
        
        // Process checkbox - will be set only if checked
        $redirect_enabled = isset($_POST['dashboard_redirect_after_login']) ? 1 : 0;
        
        // Process text field - use default if empty since 1.1.3
        $redirect_target = !empty($_POST['dashboard_redirect_target']) 
            ? sanitize_text_field($_POST['dashboard_redirect_target']) 
            : '/dashboard/';
            
        // Ensure path starts with a slash
        if (strpos($redirect_target, '/') !== 0) {
            $redirect_target = '/' . $redirect_target;
        }
        
        // Update settings in dedicated table
        $update_redirect_result = $this->update_db_setting('dashboard_redirect_after_login', $redirect_enabled);
        $update_target_result = $this->update_db_setting('dashboard_redirect_target', $redirect_target);
        
        // Debug saved values since 1.1.3
        error_log('Values saved: redirect_enabled=' . $redirect_enabled . ', redirect_target=' . $redirect_target);
        error_log('Update results: redirect=' . ($update_redirect_result ? 'success' : 'failed') . 
                 ', target=' . ($update_target_result ? 'success' : 'failed'));
        
        add_settings_error(
            'cpaas_dashboard_messages',
            'settings_updated',
            'Login redirect settings updated successfully.',
            'success'
        );
    }
    */
    
    /**
     * Update general dashboard settings
     */
    /*
    // COMMENTED OUT: Future expansion - General dashboard settings
    // Currently only using login redirect settings
    private function update_dashboard_general_settings() {
        // Future implementation for general settings
        add_settings_error(
            'cpaas_dashboard_messages',
            'settings_updated',
            'Dashboard settings updated successfully.',
            'success'
        );
    }
    */
    
    /**
     * Render dashboard overview page
     */
    /*
    // COMMENTED OUT: Future expansion - Dashboard overview page
    // Currently using simple CPaaS settings page instead
    public function render_dashboard_overview() {
        ?>
        <div class="wrap">
            <h1>CPaaS Dashboard Overview</h1>
            
            <?php settings_errors('cpaas_dashboard_messages'); ?>
            
            <div class="cpaas-admin-grid">
                <div class="cpaas-admin-card">
                    <h2>Dashboard Status</h2>
                    <p><strong>Plugin Version:</strong> <?php echo esc_html($this->version); ?></p>
                    <p><strong>Dashboard Page:</strong> 
                        <?php 
                        $dashboard_url = $this->page_manager ? $this->page_manager->get_dashboard_url() : 'Not configured';
                        echo $dashboard_url ? '<a href="' . esc_url($dashboard_url) . '" target="_blank">' . esc_html($dashboard_url) . '</a>' : 'Not configured';
                        ?>
                    </p>
                    <p><strong>PMPro Integration:</strong> 
                        <?php echo function_exists('pmpro_hasMembershipLevel') ? '✅ Active' : '❌ Not detected'; ?>
                    </p>
                </div>
                
                <div class="cpaas-admin-card">
                    <h2>Quick Actions</h2>
                    <p><a href="<?php echo admin_url('admin.php?page=cpaas-login-redirects'); ?>" class="button button-primary">Configure Login Redirects</a></p>
                    <p><a href="<?php echo admin_url('admin.php?page=cpaas-dashboard-settings'); ?>" class="button">Dashboard Settings</a></p>
                    <p><a href="<?php echo admin_url('admin.php?page=cpaas-containers'); ?>" class="button">Manage Containers</a></p>
                </div>
                
                <div class="cpaas-admin-card">
                    <h2>Development Status</h2>
                    <p><strong>Phase 2:</strong> ✅ Complete - Dashboard & PMPro Integration</p>
                    <p><strong>Phase 3:</strong> 🔄 In Progress - Container System</p>
                    <p><strong>Phase 4:</strong> ⏳ Planned - CPaaS Integration</p>
                    <p><strong>Phase 5:</strong> ⏳ Planned - Real-time Features</p>
                </div>
            </div>
        </div>
        <style>
        .cpaas-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .cpaas-admin-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        }
        .cpaas-admin-card h2 {
            margin-top: 0;
            color: #1d2327;
        }
        .cpaas-admin-card p {
            margin-bottom: 15px;
        }
        </style>
        <?php
    }
    */
    
    /**
     * Render login redirects page
     * 
     * @since 1.1.3 Updated to use dedicated settings table
     */
    public function render_login_redirects() {
        // Include diagnostic script if debug mode is enabled
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            include_once(plugin_dir_path(dirname(__FILE__)) . 'debug-settings.php');
        }
        
        // Get settings from dedicated table
        $redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        $redirect_target = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        $expired_redirect_target = $this->get_db_setting('expired_member_redirect_target', '/renew-membership/');
        
        // Log what we're displaying
        error_log("Rendering form with values: redirect_enabled=$redirect_enabled, redirect_target=$redirect_target");
        
        ?>
        <div class="wrap">
            <h1>Login Redirects</h1>
            <p>Configure how PMPro members are redirected after login.</p>
            
            <?php settings_errors('cpaas_dashboard_messages'); ?>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=cpaas-login-redirects')); ?>" class="cpaas-admin-form"> <!-- since 1.1.3 -->
                <?php
                wp_nonce_field('cpaas_admin_nonce'); // since 1.1.2
                ?>
                <table class="form-table" role="presentation"> <!-- since 1.1.3 -->
                    <tr>
                        <th scope="row"><label for="dashboard_redirect_after_login">Enable Dashboard Redirect</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Enable Dashboard Redirect</span></legend>
                                <label for="dashboard_redirect_after_login">
                                    <input name="dashboard_redirect_after_login" type="checkbox" id="dashboard_redirect_after_login" value="1" <?php checked(1, $redirect_enabled); ?>>
                                    Enable dashboard redirect for PMPro members
                                </label>
                                <p class="description">When enabled, PMPro members will be redirected to the dashboard instead of the membership account page after login.</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dashboard_redirect_target">Dashboard URL</label></th>
                        <td>
                            <input name="dashboard_redirect_target" type="text" id="dashboard_redirect_target" value="<?php echo esc_attr($redirect_target); ?>" class="regular-text">
                            <p class="description">The URL path where PMPro members should be redirected. Default: /dashboard/</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="expired_member_redirect_after_login">Enable Expired Member Redirect</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Enable Expired Member Redirect</span></legend>
                                <label for="expired_member_redirect_after_login">
                                    <input name="expired_member_redirect_after_login" type="checkbox" id="expired_member_redirect_after_login" value="1" <?php checked(1, $expired_redirect_enabled); ?>>
                                    Enable custom redirect for expired or admin-cancelled PMPro members
                                </label>
                                <p class="description">When enabled, PMPro members with expired or admin-cancelled status will be redirected to your custom renewal URL.</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="expired_member_redirect_target">Expired Member Redirect URL</label></th>
                        <td>
                            <input name="expired_member_redirect_target" type="text" id="expired_member_redirect_target" value="<?php echo esc_attr($expired_redirect_target); ?>" class="regular-text">
                            <p class="description">The URL path where expired or admin-cancelled PMPro members should be redirected. Default: /renew-membership/</p>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="action" value="update_login_redirects">
                <?php submit_button('Save Login Redirect Settings'); ?>
            </form>
            
            <p class="description" style="margin-top: 20px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cpaas-login-redirects&debug=1')); ?>" class="button button-secondary">Diagnostics</a>
                Need help troubleshooting? Click Diagnostics to check settings storage.
            </p>
        </div>
        <?php
    }
    
    /**
     * Render dashboard settings page
     */
    public function render_dashboard_settings() {
        ?>
        <div class="wrap">
            <h1>Dashboard Settings</h1>
            <p>General dashboard configuration options.</p>
            
            <?php settings_errors('cpaas_dashboard_messages'); ?>
            
            <div class="cpaas-settings-info">
                <h2>Current Implementation</h2>
                <p><strong>Template-Based System:</strong> The dashboard uses direct template loading, not shortcodes.</p>
                <p><strong>Template File:</strong> <code>templates/dashboard-template.php</code></p>
                <p><strong>Detection Method:</strong> URL-based page detection via <code>is_dashboard_page()</code></p>
                
                <h3>Future Phase 3 Settings</h3>
                <p>Container management and canvas builder settings will be available here in Phase 3.</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render container management page
     */
    public function render_container_management() {
        ?>
        <div class="wrap">
            <h1>Container Management</h1>
            <p>Phase 3 feature - Shortcode container system for CPaaS integration.</p>
            
            <div class="cpaas-phase-info">
                <h2>Coming in Phase 3</h2>
                <ul>
                    <li>Shortcode container registration system</li>
                    <li>Security isolation for container content</li>
                    <li>Drag-and-drop canvas builder</li>
                    <li>Container permission management</li>
                    <li>CPaaS integration points</li>
                </ul>
                
                <p><strong>Current Status:</strong> Phase 2 Complete - Dashboard & PMPro Integration</p>
                <p><strong>Next Step:</strong> Implement container system architecture</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render login redirect section
     */
    /*
    // COMMENTED OUT: Future expansion - Settings section callback
    // Currently using custom table method instead of WordPress Settings API
    public function render_login_redirect_section() {
        echo '<p>Configure how PMPro members are redirected after successful login. These settings override PMPro\'s default account page redirect.</p>';
    }
    */
    
    /**
     * Create dedicated settings table for better reliability
     * 
     * @since 1.1.3
     * @return void
     */
    public function create_settings_table() {
        global $wpdb;
        
        // Use better naming convention for table - follows PMPro patterns
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            // Enhanced table structure with better indexing and security
            $sql = "CREATE TABLE $table_name (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                setting_key varchar(191) NOT NULL,
                setting_value longtext NOT NULL,
                user_id bigint(20) unsigned DEFAULT 0 NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY setting_key (setting_key),
                KEY user_id (user_id),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            // Use dbDelta for reliable table creation/updates - PMPro pattern
            dbDelta($sql);
            
            // Log table creation with timestamp for security audit
            error_log(sprintf('[%s] CPaaS Login Redirect: Settings table created', current_time('mysql')));
            
            // Insert default settings with proper sanitization
            $this->insert_default_settings($table_name);
        }
    }
    
    /**
     * Insert default settings into the dedicated table with enhanced security
     * 
     * @since 1.1.3
     * @param string $table_name The table name
     * @return void
     */
    private function insert_default_settings($table_name) {
        global $wpdb;
        
        // Get existing settings from options table with proper sanitization
        $redirect_enabled = absint(get_option('dashboard_redirect_after_login', 1));
        $redirect_target = sanitize_text_field(get_option('dashboard_redirect_target', '/dashboard/'));
        $expired_redirect_enabled = absint(get_option('expired_member_redirect_after_login', 0));
        $expired_redirect_target = sanitize_text_field(get_option('expired_member_redirect_target', '/renew-membership/'));
        
        // Ensure paths are properly formatted for security
        if (strpos($redirect_target, '/') !== 0) {
            $redirect_target = '/' . $redirect_target;
        }
        if (strpos($expired_redirect_target, '/') !== 0) {
            $expired_redirect_target = '/' . $expired_redirect_target;
        }
        
        // Current user or admin
        $user_id = get_current_user_id();
        if (!$user_id) {
            $user_id = 1; // Default to admin if not logged in during install
        }
        
        // Current timestamp for audit trail
        $timestamp = current_time('mysql');
        
        // Use wpdb prepared statements for security - follows Defender patterns
        $redirect_result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (setting_key, setting_value, user_id, created_at, updated_at) 
            VALUES (%s, %s, %d, %s, %s)
            ON DUPLICATE KEY UPDATE setting_value = %s, user_id = %d, updated_at = %s",
            'dashboard_redirect_after_login',
            $redirect_enabled ? '1' : '0',
            $user_id,
            $timestamp,
            $timestamp,
            $redirect_enabled ? '1' : '0',
            $user_id,
            $timestamp
        ));
        
        $target_result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (setting_key, setting_value, user_id, created_at, updated_at) 
            VALUES (%s, %s, %d, %s, %s)
            ON DUPLICATE KEY UPDATE setting_value = %s, user_id = %d, updated_at = %s",
            'dashboard_redirect_target',
            $redirect_target,
            $user_id,
            $timestamp,
            $timestamp,
            $redirect_target,
            $user_id,
            $timestamp
        ));
        
        // Insert expired member redirect settings
        $expired_redirect_result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (setting_key, setting_value, user_id, created_at, updated_at) 
            VALUES (%s, %s, %d, %s, %s)
            ON DUPLICATE KEY UPDATE setting_value = %s, user_id = %d, updated_at = %s",
            'expired_member_redirect_after_login',
            $expired_redirect_enabled ? '1' : '0',
            $user_id,
            $timestamp,
            $timestamp,
            $expired_redirect_enabled ? '1' : '0',
            $user_id,
            $timestamp
        ));
        
        $expired_target_result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (setting_key, setting_value, user_id, created_at, updated_at) 
            VALUES (%s, %s, %d, %s, %s)
            ON DUPLICATE KEY UPDATE setting_value = %s, user_id = %d, updated_at = %s",
            'expired_member_redirect_target',
            $expired_redirect_target,
            $user_id,
            $timestamp,
            $timestamp,
            $expired_redirect_target,
            $user_id,
            $timestamp
        ));
        
        // Log with security context for audit trail
        error_log(sprintf(
            '[%s] CPaaS Login Redirect: Default settings initialized by user %d. Results: redirect=%s, target=%s, expired_redirect=%s, expired_target=%s', 
            current_time('mysql'),
            $user_id,
            $redirect_result ? 'success' : 'failed (' . $wpdb->last_error . ')',
            $target_result ? 'success' : 'failed (' . $wpdb->last_error . ')',
            $expired_redirect_result ? 'success' : 'failed (' . $wpdb->last_error . ')',
            $expired_target_result ? 'success' : 'failed (' . $wpdb->last_error . ')'
        ));
    }
    
    /**
     * Get a setting value from the dedicated settings table with enhanced security
     * 
     * @since 1.1.3 Updated with security improvements
     * @param string $setting_key The setting key
     * @param mixed $default Default value if setting not found
     * @return mixed The setting value or default
     */
    public function get_db_setting($setting_key, $default = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        
        // Sanitize input key for security
        $setting_key = sanitize_key($setting_key);
        
        // Use prepared statement for security (Defender pattern)
        $query = $wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s LIMIT 1",
            $setting_key
        );
        
        // Only log setting retrieval failures, not successful retrievals
        
        // Get the setting with caching for performance (PMPro pattern)
        $cache_key = 'cpaas_login_redirect_' . md5($setting_key);
        $result = wp_cache_get($cache_key);
        
        if (false === $result) {
            $result = $wpdb->get_var($query);
            
            // Cache the result for performance
            if (null !== $result) {
                wp_cache_set($cache_key, $result, 'cpaas_settings', HOUR_IN_SECONDS);
            }
        }
        
        // If not found, try to get from options as fallback
        if (null === $result) {
            $result = get_option($setting_key, $default);
            
            // If found in options, migrate it to our table for future use
            if ($result !== $default) {
                $this->update_db_setting($setting_key, $result);
            }
        }
        
        return (null === $result) ? $default : $result;
    }
    
    /**
     * Update a setting value in the dedicated settings table with enhanced security
     * 
     * @since 1.1.3 Updated with security improvements
     * @param string $setting_key The setting key
     * @param mixed $value The setting value
     * @return bool True on success, false on failure
     */
    public function update_db_setting($setting_key, $value) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        
        // Sanitize input key for security
        $setting_key = sanitize_key($setting_key);
        
        // Current user for audit trail
        $user_id = get_current_user_id();
        if (!$user_id) {
            $user_id = 1; // Default to admin if not logged in during install
        }
        
        // Current timestamp for audit trail
        $timestamp = current_time('mysql');
        
        // Serialize arrays/objects, convert other types to string
        if (is_array($value) || is_object($value)) {
            $value = maybe_serialize($value);
        } else {
            $value = (string)$value;
        }
        
        // Check if setting exists using prepared statement
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE setting_key = %s",
                $setting_key
            )
        );
        
        $result = false;
        
        // Insert or update based on existence
        if ($exists) {
            // Update with prepared statement
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_name SET setting_value = %s, user_id = %d, updated_at = %s WHERE setting_key = %s",
                    $value,
                    $user_id,
                    $timestamp,
                    $setting_key
                )
            );
        } else {
            // Insert with prepared statement
            $result = $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO $table_name (setting_key, setting_value, user_id, created_at, updated_at) VALUES (%s, %s, %d, %s, %s)",
                    $setting_key,
                    $value,
                    $user_id,
                    $timestamp,
                    $timestamp
                )
            );
        }
        
        // Clear cache for this setting
        $cache_key = 'cpaas_login_redirect_' . md5($setting_key);
        wp_cache_delete($cache_key, 'cpaas_settings');
        
        // Update legacy option as well for compatibility
        update_option($setting_key, $value);
        
        // Only log errors, not successful updates to reduce log noise
        if (false === $result && defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[CPaaS Login Redirect] ERROR updating setting "%s": %s',
                $setting_key,
                $wpdb->last_error
            ));
        }
        
        return false !== $result;
    }
    
    /**
     * Handle settings form submission with enhanced security
     * 
     * @since 1.1.0
     * @since 1.1.3 Updated with security improvements
     */
    public function handle_settings_form() {
        // Security check - verify nonce for CSRF protection
        if (!isset($_POST['dashboard_settings_nonce']) || 
            !wp_verify_nonce($_POST['dashboard_settings_nonce'], 'save_dashboard_settings')) {
            wp_die(__('Security check failed', 'dashboard-plugin'), 'Security Error', array('response' => 403));
        }
        
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dashboard-plugin'), 'Permission Error', array('response' => 403));
        }
        
        // Rate limiting check - protect against brute force attacks
        $rate_limit_key = 'cpaas_settings_submission_' . get_current_user_id();
        $attempt_count = get_transient($rate_limit_key);
        
        if (false !== $attempt_count && $attempt_count >= 5) {
            wp_die(__('Too many settings submission attempts. Please try again later.', 'dashboard-plugin'), 'Rate Limit Error', array('response' => 429));
        }
        
        // Increment attempt count
        if (false === $attempt_count) {
            set_transient($rate_limit_key, 1, 5 * MINUTE_IN_SECONDS); // 5 minute window
        } else {
            set_transient($rate_limit_key, $attempt_count + 1, 5 * MINUTE_IN_SECONDS);
        }
        
        // Start tracking changes for logging
        $changes = array();
        
        // Process redirect checkbox with sanitization
        $redirect_enabled = isset($_POST['dashboard_redirect_after_login']) ? 1 : 0;
        $old_redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        
        if ($redirect_enabled != $old_redirect_enabled) {
            $this->update_db_setting('dashboard_redirect_after_login', $redirect_enabled);
            $changes['dashboard_redirect_after_login'] = array(
                'old' => $old_redirect_enabled,
                'new' => $redirect_enabled
            );
        }
        
        // Process redirect URL with sanitization
        if (isset($_POST['dashboard_redirect_target'])) {
            $redirect_url = sanitize_text_field($_POST['dashboard_redirect_target']);
            
            // Ensure path starts with forward slash for consistency
            if (!empty($redirect_url) && strpos($redirect_url, '/') !== 0) {
                $redirect_url = '/' . $redirect_url;
            }
            
            $old_redirect_url = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
            
            if ($redirect_url !== $old_redirect_url) {
                $this->update_db_setting('dashboard_redirect_target', $redirect_url);
                $changes['dashboard_redirect_target'] = array(
                    'old' => $old_redirect_url,
                    'new' => $redirect_url
                );
            }
        }
        
        // Process expired member redirect checkbox with sanitization
        $expired_redirect_enabled = isset($_POST['expired_member_redirect_after_login']) ? 1 : 0;
        $old_expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        
        if ($expired_redirect_enabled != $old_expired_redirect_enabled) {
            $this->update_db_setting('expired_member_redirect_after_login', $expired_redirect_enabled);
            $changes['expired_member_redirect_after_login'] = array(
                'old' => $old_expired_redirect_enabled,
                'new' => $expired_redirect_enabled
            );
        }
        
        // Process expired member redirect URL with sanitization
        if (isset($_POST['expired_member_redirect_target'])) {
            $expired_redirect_url = sanitize_text_field($_POST['expired_member_redirect_target']);
            
            // Ensure path starts with forward slash for consistency
            if (!empty($expired_redirect_url) && strpos($expired_redirect_url, '/') !== 0) {
                $expired_redirect_url = '/' . $expired_redirect_url;
            }
            
            $old_expired_redirect_url = $this->get_db_setting('expired_member_redirect_target', '/renew-membership/');
            
            if ($expired_redirect_url !== $old_expired_redirect_url) {
                $this->update_db_setting('expired_member_redirect_target', $expired_redirect_url);
                $changes['expired_member_redirect_target'] = array(
                    'old' => $old_expired_redirect_url,
                    'new' => $expired_redirect_url
                );
            }
        }
        
        // Log all changes for audit trail
        if (!empty($changes)) {
            $changes_log = sprintf(
                '[%s] CPaaS Login Redirect: Settings updated by user %d from IP %s. Changes: %s',
                current_time('mysql'),
                get_current_user_id(),
                $this->get_client_ip(),
                wp_json_encode($changes)
            );
            error_log($changes_log);
        }
        
        // Clear rate limiting on successful submission
        delete_transient($rate_limit_key);
        
        // Redirect back to settings page with success message
        wp_safe_redirect(add_query_arg('settings-updated', 'true', admin_url('admin.php?page=cpaas-settings')));
        exit;
    }
    
    /**
     * Check PMPro member status for expired or admin-cancelled memberships
     * 
     * @since 1.1.3
     * @param int $user_id User ID to check
     * @return string Member status: 'active', 'expired', 'admin_cancelled', or 'none'
     */
    private function get_pmpro_member_status($user_id) {
        global $wpdb;
        
        if (!function_exists('pmpro_hasMembershipLevel')) {
            return 'none';
        }
        
        // Check if user has active membership first
        if (pmpro_hasMembershipLevel(null, $user_id)) {
            return 'active';
        }
        
        // Check for latest status in wp_pmpro_memberships_users table based on 'modified' column
        $latest_status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$wpdb->prefix}pmpro_memberships_users 
             WHERE user_id = %d 
             ORDER BY modified DESC 
             LIMIT 1",
            $user_id
        ));
        
        // Return the latest status if found, otherwise 'none'
        if ($latest_status) {
            return $latest_status;
        }
        
        return 'none';
    }
    
    /**
     * Get client IP address with proxy support
     * 
     * @since 1.1.3
     * @return string Client IP address
     */
    private function get_client_ip() {
        // Check for CloudFlare IP
        $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']) : '';
        
        // Check for proxy headers
        if (empty($ip) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Get the first IP in the list
            $ip_list = explode(',', sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
            $ip = trim($ip_list[0]);
        }
        
        // Fallback to remote addr
        if (empty($ip)) {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0';
        }
        
        return $ip;
    }
    
    /**
     * Add the settings page to the admin menu
     * 
     * @since 1.1.0
     * @since 1.1.3 Added diagnostics page
     * @since 1.1.4 Renamed to CPaaS and moved to top position     */
    public function add_settings_page() {
        add_menu_page(
            'CPaaS Settings',
            'CPaaS',
            'manage_options',
            'cpaas-settings',
            array($this, 'render_settings_page'),
            'dashicons-cloud',
            3
        );
        
        // Add submenu items
        add_submenu_page(
            'cpaas-settings',
            'Login Redirects',
            'Login Redirects',
            'manage_options',
            'cpaas-settings'
        );
        
        // Add diagnostics submenu
        add_submenu_page(
            'cpaas-settings',
            'Login Redirect Diagnostics',
            'Diagnostics',
            'manage_options',
            'cpaas-diagnostics',
            array($this, 'render_diagnostics_page')
        );
    }
    
    /**
     * Render the diagnostics page
     * 
     * @since 1.1.3
     */
    public function render_diagnostics_page() {
        // Simple diagnostics display
        $redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        $dashboard_target = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $expired_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        $expired_target = $this->get_db_setting('expired_member_redirect_target', '/membership-account/');
        
        ?>
        <div class="wrap">
            <h1>CPaaS Login Redirect Diagnostics</h1>
            
            <div class="notice notice-info">
                <p><strong>Current Settings:</strong></p>
                <ul>
                    <li>Dashboard Redirect Enabled: <?php echo $redirect_enabled ? 'Yes' : 'No'; ?></li>
                    <li>Dashboard Target: <?php echo esc_html($dashboard_target); ?></li>
                    <li>Expired Redirect Enabled: <?php echo $expired_enabled ? 'Yes' : 'No'; ?></li>
                    <li>Expired Target: <?php echo esc_html($expired_target); ?></li>
                </ul>
            </div>
            
            <div class="notice notice-warning">
                <p><strong>PMPro Status:</strong> <?php echo function_exists('pmpro_hasMembershipLevel') ? 'Active' : 'Not Available'; ?></p>
            </div>
            
            <div class="notice notice-success">
                <p><strong>Database Table:</strong> <?php echo $this->settings_table_exists() ? 'Exists' : 'Missing'; ?></p>
            </div>
            
            <p><a href="<?php echo admin_url('admin.php?page=cpaas-settings'); ?>" class="button button-primary">← Back to Settings</a></p>
        </div>
        <?php
    }
    
    /**
     * Check if settings table exists
     *
     * @since 1.1.4
     * @return bool
     */
    private function settings_table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        return $result === $table_name;
    }
    
    /**
     * Handles database version upgrades
     *
     * @since 1.1.3
     */
    public function maybe_upgrade_database() {
        $current_db_version = get_option('cpaas_login_redirect_db_version', '0');
        
        if (version_compare($current_db_version, '1.1', '<')) {
            // Perform upgrade to 1.1
            $this->create_settings_table();
            update_option('cpaas_login_redirect_db_version', '1.1');
            error_log('[CPaaS Login Redirect] Database upgraded to version 1.1');
        }
        
        if (version_compare($current_db_version, '1.3', '<')) {
            // Handle migration from old table name if it exists
            global $wpdb;
            $old_table = $wpdb->prefix . 'dashboard_settings';
            $new_table = $wpdb->prefix . 'cpaas_login_redirect_settings';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$old_table'") == $old_table) {
                // Copy data from old table to new table
                $old_data = $wpdb->get_results("SELECT * FROM $old_table", ARRAY_A);
                
                if (!empty($old_data)) {
                    foreach ($old_data as $row) {
                        if (isset($row['setting_name'])) {
                            $this->update_db_setting(
                                $row['setting_name'],
                                $row['setting_value']
                            );
                        }
                    }
                }
                
                // Optionally drop old table after successful migration
                // $wpdb->query("DROP TABLE IF EXISTS $old_table");
                
                error_log('[CPaaS Login Redirect] Migrated settings from old table');
            }
            
            update_option('cpaas_login_redirect_db_version', '1.3');
        }
    }
    
    /**
     * AJAX handler for getting a setting
     *
     * @since 1.1.3
     */
    public function ajax_get_setting() {
        // Security check
        check_ajax_referer('dashboard_ajax_nonce', 'nonce');
        
        // Only allow administrators
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get the setting key
        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';
        
        if (empty($key)) {
            wp_send_json_error('No setting key provided');
        }
        
        // Get the setting value
        $value = $this->get_db_setting($key, '');
        
        wp_send_json_success(array(
            'key' => $key,
            'value' => $value
        ));
    }
    
    /**
     * AJAX handler for updating a setting
     *
     * @since 1.1.3
     */
    public function ajax_update_setting() {
        // Security check
        check_ajax_referer('dashboard_ajax_nonce', 'nonce');
        
        // Only allow administrators
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get the setting key and value
        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';
        $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
        
        if (empty($key)) {
            wp_send_json_error('No setting key provided');
        }
        
        // Update the setting
        $result = $this->update_db_setting($key, $value);
        
        if ($result) {
            wp_send_json_success(array(
                'key' => $key,
                'value' => $value,
                'message' => 'Setting updated successfully'
            ));
        } else {
            wp_send_json_error('Failed to update setting');
        }
    }
    
    /**
     * Override PMPro redirect to use our custom redirect settings
     * 
     * @since 1.1.3
     * @param string $redirect_to The URL to redirect to
     * @param string $requested_redirect_to The requested redirect URL
     * @param WP_User|false $user The logged in user, or false if no user is logged in
     * @return string The URL to redirect to
     */
    public function pmpro_override_redirect($redirect_to, $requested_redirect_to = '', $user = false) {
        // Reduced logging - only log actual redirects and errors
        
        // FIXED: Better user validation to prevent WP_Error issues
        if (!$user || !is_object($user) || is_wp_error($user) || !isset($user->ID)) {
            return $redirect_to;
        }
        
        // Check if our redirect is enabled
        $redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        
        if (!$redirect_enabled) {
            return $redirect_to;
        }
        
        // Admin users can always access admin areas
        if (current_user_can('manage_options')) {
            return $redirect_to;
        }
        
        // SECURITY CHECK: Check PMPro member status
        $member_status = $this->get_pmpro_member_status($user->ID);
        
        // Get our custom redirect targets
        $dashboard_redirect_target = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        $expired_redirect_target = $this->get_db_setting('expired_member_redirect_target', '/membership-account/');
        
        $site_url = get_site_url();
        
        // Keep only essential redirect success logging
        
        if ($member_status === 'active') {
            // SCENARIO 1: Active PMPro member - redirect to dashboard
            $full_redirect = $site_url . $dashboard_redirect_target;
            
            // Log successful redirect for active members
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('[CPaaS] Active PMPro member redirected: User ' . $user->ID . ' → ' . $full_redirect);
            }
            return $full_redirect;
        } else {
            // SCENARIO 2: Expired/admin_cancelled/non-PMPro members
            if ($expired_redirect_enabled) {
                $expired_full_redirect = $site_url . $expired_redirect_target;
                
                // Log successful redirect for non-active members
                if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    error_log('[CPaaS] Non-active member redirected: User ' . $user->ID . ' (' . $member_status . ') → ' . $expired_full_redirect);
                }
                return $expired_full_redirect;
            } else {
                // Expired redirect disabled, use default redirect
                return $redirect_to;
            }
        }
    }
    
    /**
     * Check if current user can access dashboard page
     * 
     * @since 1.1.3
     * @return void
     */
    public function check_dashboard_access() {
        // Only check on frontend dashboard pages
        if (is_admin()) {
            return;
        }
        
        // Get current page information
        global $post;
        if (!$post) {
            return;
        }
        
        // Check if this is a dashboard page
        $is_dashboard = false;
        
        // Check page content for dashboard shortcode
        if (has_shortcode($post->post_content, 'dashboard')) {
            $is_dashboard = true;
        }
        
        // Check page slug/URL
        $dashboard_path = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $current_path = $_SERVER['REQUEST_URI'];
        
        if (strpos($current_path, $dashboard_path) !== false) {
            $is_dashboard = true;
        }
        

        
        // If not a dashboard page, no need to check access
        if (!$is_dashboard) {
            return;
        }
        
        // Check if user can access dashboard
        $user_id = get_current_user_id();
        
        // Not logged in - redirect to login
        if (!$user_id) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
        
        // Admin users can always access
        if (current_user_can('manage_options')) {
            return;
        }
        
        // SECURITY CHECK: Only allow active PMPro members to access dashboard
        if (function_exists('pmpro_hasMembershipLevel')) {
            if (!pmpro_hasMembershipLevel(null, $user_id)) {
                // User is not an active PMPro member - redirect based on settings
                $expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
                
                if ($expired_redirect_enabled) {
                    $expired_redirect_target = $this->get_db_setting('expired_member_redirect_target', '/membership-account/');
                    $redirect_url = home_url($expired_redirect_target);
                } else {
                    $redirect_url = home_url('/membership-account/');
                }
                
                // Log only access denials, not successful access checks
                wp_redirect($redirect_url);
                exit;
            }
        } else {
            // PMPro not available - deny access to dashboard
            // PMPro not available - this is a configuration issue that should be logged
            $redirect_url = home_url('/');
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Custom login redirect handler
     * 
     * @since 1.1.3
     * @param string $redirect_to The URL to redirect to
     * @param string $requested_redirect_to The requested redirect URL
     * @param WP_User|false $user The logged in user, or false if no user is logged in
     * @return string The URL to redirect to
     */
    public function custom_login_redirect($redirect_to, $requested_redirect_to, $user) {
        // FIXED: Better user validation to prevent WP_Error issues
        if (!$user || !is_object($user) || is_wp_error($user) || !isset($user->ID)) {
            return $redirect_to;
        }
        
        // Check if our redirect is enabled
        $redirect_enabled = $this->get_db_setting('dashboard_redirect_after_login', 1);
        
        if (!$redirect_enabled) {
            return $redirect_to;
        }
        
        // Admin users can override with requested redirect
        if (current_user_can('manage_options') && !empty($requested_redirect_to)) {
            return $requested_redirect_to;
        }
        
        // SECURITY CHECK: Check PMPro member status
        $member_status = $this->get_pmpro_member_status($user->ID);
        
        // Get our custom redirect targets
        $dashboard_redirect_target = $this->get_db_setting('dashboard_redirect_target', '/dashboard/');
        $expired_redirect_enabled = $this->get_db_setting('expired_member_redirect_after_login', 0);
        $expired_redirect_target = $this->get_db_setting('expired_member_redirect_target', '/membership-account/');
        
        $site_url = get_site_url();
        
        if ($member_status === 'active') {
            // SCENARIO 1: Active PMPro member - redirect to dashboard
            $full_redirect = $site_url . $dashboard_redirect_target;
            

            return $full_redirect;
        } else {
            // SCENARIO 2: Expired/admin_cancelled/non-PMPro members
            if ($expired_redirect_enabled) {
                $expired_full_redirect = $site_url . $expired_redirect_target;
                

                return $expired_full_redirect;
            } else {
                // Expired redirect disabled, use default redirect
                return $redirect_to;
            }
        }
    }
    
    /**
     * Clear all caches and transients - Debugging utility
     * 
     * @since 1.1.3
     * @return void
     */
    public function clear_all_caches() {
        // Clear WordPress object cache
        wp_cache_flush();
        
        // Clear our plugin-specific cache group
        wp_cache_flush_group('cpaas_settings');
        
        // Clear our specific setting transients
        $setting_keys = [
            'dashboard_redirect_after_login',
            'dashboard_redirect_target', 
            'expired_member_redirect_after_login',
            'expired_member_redirect_target'
        ];
        
        foreach ($setting_keys as $key) {
            $cache_key = 'cpaas_login_redirect_' . md5($key);
            wp_cache_delete($cache_key, 'cpaas_settings');
            delete_transient($cache_key);
            delete_transient('cpaas_' . $key);
        }
        
        // Clear any PMPro related caches
        if (function_exists('pmpro_flush_cache')) {
            pmpro_flush_cache();
        }
        
        // Clear WordPress user meta cache
        wp_cache_delete_multiple([], 'user_meta');
        
        // Clear general transients
        delete_transient('cpaas_settings_cache');
        delete_transient('dashboard_settings_cache');
        
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        // Cache clearing completed silently
    }
    
    /**
     * Add URL-based cache clearing utility
     * 
     * @since 1.1.3
     * @return void
     */
    public function handle_cache_clear_request() {
        if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == 'true') {
            // Security check - only allow administrators
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to clear caches.', 'dashboard-plugin'), 'Permission Error', array('response' => 403));
            }
            
            // Optional nonce check for extra security (if nonce is provided)
            if (isset($_GET['_wpnonce']) && !wp_verify_nonce($_GET['_wpnonce'], 'clear_cache_nonce')) {
                wp_die(__('Security check failed.', 'dashboard-plugin'), 'Security Error', array('response' => 403));
            }
            
            // Clear all caches
            $this->clear_all_caches();
            
            // Remove the clear_cache parameter and redirect
            $redirect_url = remove_query_arg(array('clear_cache', '_wpnonce'), $_SERVER['REQUEST_URI']);
            $redirect_url = add_query_arg('cache_cleared', 'true', $redirect_url);
            
            wp_redirect($redirect_url);
            exit;
        }
    }
} 