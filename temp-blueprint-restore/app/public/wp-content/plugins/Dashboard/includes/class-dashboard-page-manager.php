<?php
namespace Dashboard\Core;

use Dashboard\Security\Security_Manager;

/**
 * Dashboard Plugin - Page Manager v1.0.5
 * 
 * Fixed admin redirect interference following WordPress security best practices
 * Added proper is_admin() and admin URL checks to prevent WP admin lockout
 * Enhanced capability checking for better security separation
 * 
 * @since 1.0.5
 */
class Dashboard_Page_Manager {
    private $security;
    private $dashboard_page_id;
    private $version;
    
    public function __construct($version = '1.0.5') {
        $this->version = $version;
        $this->security = new Security_Manager();
        $this->dashboard_page_id = get_option('dashboard_page_id');
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks for dashboard functionality v1.0.8
     * 
     * Fixed PMPro redirect conflict with comprehensive hook integration
     * Added debugging to track redirect flow
     * Enhanced capability checking for better security separation
     * 
     * @since 1.0.8 - Enhanced PMPro integration with debugging
     */
    private function init_hooks() {
        // Enhanced login redirect hooks with HIGHEST priority (99 vs PMPro's 10)
        add_filter('wp_login_redirect', [$this, 'redirect_after_login'], 99, 3);
        add_filter('login_redirect', [$this, 'redirect_after_login'], 99, 3);
        
        // PMPro specific hooks for complete integration
        add_filter('pmpro_login_redirect_url', [$this, 'pmpro_redirect_integration'], 99, 3);
        add_filter('pmpro_login_redirect', [$this, 'pmpro_login_redirect_override'], 99);
        
        // Additional PMPro hooks for edge cases
        add_action('wp_login', [$this, 'handle_wp_login'], 10, 2);
        add_action('template_redirect', [$this, 'fallback_redirect_check'], 5);
        
        // CRITICAL FIX: Only register template hooks for frontend
        if (!is_admin()) {
            add_action('template_redirect', [$this, 'redirect_to_dashboard']);
            add_action('template_redirect', [$this, 'load_dashboard_template']);
            add_filter('template_include', [$this, 'dashboard_template_override']);
            
            // Asset enqueueing (frontend only)
            add_action('wp_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        }
        
        // Admin-specific hooks (admin only)
        if (is_admin()) {
            add_action('admin_notices', [$this, 'dashboard_admin_notices']);
        }
        
        // Dashboard page creation (safe for all contexts)
        add_action('after_setup_theme', [$this, 'maybe_create_dashboard_page']);
    }
    
    /**
     * Create dashboard page if it doesn't exist
     * Following WordPress best practices for page creation
     */
    public function maybe_create_dashboard_page() {
        // Check if page already exists
        if ($this->dashboard_page_id && get_post($this->dashboard_page_id)) {
            return;
        }
        
        // Create dashboard page with custom template approach (not shortcode)
        $page_data = [
            'post_title' => 'Dashboard',
            'post_content' => '<!-- Dashboard content will be loaded via template -->',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_name' => 'dashboard'
        ];
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            update_option('dashboard_page_id', $page_id);
            
            // Add custom page template meta
            update_post_meta($page_id, '_wp_page_template', 'dashboard-template.php');
            
            // Set as front page only if setting is enabled
            if (get_option('dashboard_set_as_homepage', 0)) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $page_id);
            }
            
            // Set transient for admin notice
            set_transient('dashboard_page_created', $page_id, 300); // 5 minutes
            
            // Flush rewrite rules
            flush_rewrite_rules();
        }
    }
    
    /**
     * Add custom rewrite rules for dashboard
     */
    public function add_dashboard_rewrite_rules() {
        add_rewrite_rule(
            '^dashboard/?$',
            'index.php?pagename=dashboard',
            'top'
        );
        
        add_rewrite_rule(
            '^dashboard/([^/]+)/?$',
            'index.php?pagename=dashboard&dashboard_section=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%dashboard_section%', '([^&]+)');
    }
    
    /**
     * Enqueue dashboard assets only on dashboard pages
     */
    public function enqueue_dashboard_assets() {
        // Only enqueue on dashboard pages
        if (!$this->is_dashboard_page()) {
            return;
        }
        
        // Check user access
        if (!$this->security->user_can_access_dashboard()) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'dashboard-ghl-style',
            plugin_dir_url(__DIR__) . 'assets/css/dashboard-ghl-style.css',
            [],
            $this->version
        );
        
        // Enqueue JavaScript (correct filename)
        wp_enqueue_script(
            'dashboard-navigation',
            plugin_dir_url(__DIR__) . 'assets/js/dashboard-navigation.js',
            ['jquery'],
            $this->version,
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('dashboard-navigation', 'dashboardConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dashboard_ajax_nonce'),
            'userId' => get_current_user_id(),
            'section' => get_query_var('dashboard_section', 'overview'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'version' => $this->version,
            'pluginUrl' => plugin_dir_url(__DIR__)
        ]);
    }
    
    /**
     * Load dashboard template instead of shortcode approach
     */
    public function load_dashboard_template() {
        if (!$this->is_dashboard_page()) {
            return;
        }
        
        // Check user access first
        if (!$this->security->user_can_access_dashboard()) {
            $this->show_access_denied();
            return;
        }
        
        // Set dashboard-specific query vars
        global $wp_query;
        $wp_query->set('is_dashboard', true);
        $wp_query->set('dashboard_section', get_query_var('dashboard_section', 'overview'));
    }
    
    /**
     * Override template for dashboard page
     */
    public function dashboard_template_override($template) {
        if (!$this->is_dashboard_page()) {
            return $template;
        }
        
        // Look for dashboard template in theme first, then plugin
        $dashboard_template = locate_template(['dashboard-template.php']);
        
        if (!$dashboard_template) {
            $dashboard_template = plugin_dir_path(__DIR__) . 'templates/dashboard-template.php';
        }
        
        if (file_exists($dashboard_template)) {
            return $dashboard_template;
        }
        
        return $template;
    }
    
    /**
     * Check if current page is the dashboard page
     */
    public function is_dashboard_page() {
        global $post;
        
        if (!$post) {
            // Check for dashboard URL pattern
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            return strpos($request_uri, '/dashboard') !== false;
        }
        
        // Check if current page is the dashboard page
        if ($this->dashboard_page_id && $post->ID == $this->dashboard_page_id) {
            return true;
        }
        
        // Check for dashboard slug
        return $post->post_name === 'dashboard';
    }
    
    /**
     * Redirect users after login v1.0.5
     * 
     * Enhanced admin area protection following WordPress best practices
     * Multiple layers of admin detection per Context7 WordPress security patterns
     * Prevents interference with WordPress admin functionality
     * 
     * @since 1.0.5 - Enhanced admin protection, removed debug logging
     */
    public function redirect_after_login($redirect_to, $requested_redirect_to, $user) {
        // Check if user has WP_Error (login failed)
        if (is_wp_error($user)) {
            return $redirect_to;
        }
        
        // Ensure we have a valid user object
        if (!$user || !isset($user->ID)) {
            return $redirect_to;
        }
        
        // CRITICAL: Multiple layers of admin area protection
        // Layer 1: Check if user has admin capabilities
        if (user_can($user, 'manage_options')) {
            return $redirect_to;
        }
        
        // Layer 2: Check if user can edit posts (editors and above)
        if (user_can($user, 'edit_posts')) {
            return $redirect_to;
        }
        
        // Layer 3: Check for admin URL patterns in redirect destinations
        $admin_urls = [
            '/wp-admin/',
            'wp-admin',
            'admin.php',
            'edit.php',
            'users.php',
            'tools.php',
            'options-general.php'
        ];
        
        foreach ($admin_urls as $admin_url) {
            if (strpos($redirect_to, $admin_url) !== false || strpos($requested_redirect_to, $admin_url) !== false) {
                return $redirect_to;
            }
        }
        
        // Layer 4: Check if currently in admin area
        if (is_admin()) {
            return $redirect_to;
        }
        
        // Layer 5: Check request URI for admin patterns
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        foreach ($admin_urls as $admin_url) {
            if (strpos($request_uri, $admin_url) !== false) {
                return $redirect_to;
            }
        }
        
        // Check if redirect is enabled
        if (!get_option('dashboard_redirect_after_login', 1)) {
            return $redirect_to;
        }
        
        // Check user access to dashboard
        if (!$this->security->user_can_access_dashboard($user->ID)) {
            return $redirect_to;
        }
        
        // Get dashboard page URL
        $dashboard_url = $this->get_dashboard_url();
        
        if (!$dashboard_url) {
            return $redirect_to;
        }
        
        return esc_url($dashboard_url);
    }
    
    /**
     * Redirect to dashboard if user should be redirected v1.0.5
     * 
     * Enhanced admin area protection to prevent WordPress admin interference
     * Multiple security layers following WordPress best practices
     * Only runs on frontend to avoid admin area conflicts
     * 
     * @since 1.0.5 - Enhanced admin protection, removed debug logging
     */
    public function redirect_to_dashboard() {
        // CRITICAL: Never redirect if in admin area
        if (is_admin()) {
            return;
        }
        
        // CRITICAL: Check for admin URL patterns in request
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $admin_urls = [
            '/wp-admin/',
            'wp-admin',
            'wp-login.php',
            'wp-cron.php',
            'xmlrpc.php'
        ];
        
        foreach ($admin_urls as $admin_url) {
            if (strpos($request_uri, $admin_url) !== false) {
                return;
            }
        }
        
        // CRITICAL: Never redirect admin users
        if (current_user_can('manage_options') || current_user_can('edit_posts')) {
            return;
        }
        
        // Only proceed if user is logged in
        if (!is_user_logged_in()) {
            return;
        }
        
        // Only redirect if on the actual dashboard page and user should see custom dashboard
        if ($this->is_dashboard_page() && $this->security->user_can_access_dashboard()) {
            // Don't redirect here, just let template system handle it
            return;
        }
    }
    
    /**
     * Get dashboard page URL
     */
    public function get_dashboard_url() {
        if ($this->dashboard_page_id) {
            return get_permalink($this->dashboard_page_id);
        }
        
        // Get URL from admin settings instead of hardcoded /dashboard/
        global $wpdb;
        $table_name = $wpdb->prefix . 'cpaas_login_redirect_settings';
        $dashboard_url = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s LIMIT 1",
            'dashboard_redirect_target'
        ));
        
        if ($dashboard_url) {
            return home_url($dashboard_url);
        }
        
        // Final fallback
        return home_url('/dashboard/');
    }
    
    /**
     * Show access denied message
     */
    private function show_access_denied() {
        // Set HTTP status
        status_header(403);
        
        // Load access denied template
        $template = locate_template(['dashboard-access-denied.php']);
        
        if (!$template) {
            $template = plugin_dir_path(__DIR__) . 'templates/dashboard-access-denied.php';
        }
        
        if (file_exists($template)) {
            include $template;
            exit;
        }
        
        // Fallback message
        wp_die(
            '<h1>Access Denied</h1><p>You need an active membership to access the dashboard.</p>',
            'Dashboard Access Denied',
            ['response' => 403]
        );
    }
    
    /**
     * Show admin notices
     */
    public function dashboard_admin_notices() {
        // Dashboard page created notice
        $page_id = get_transient('dashboard_page_created');
        if ($page_id) {
            $page_url = get_permalink($page_id);
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Dashboard Plugin:</strong> Dashboard page created successfully! ';
            echo '<a href="' . esc_url($page_url) . '" target="_blank">View Dashboard</a> | ';
            echo '<a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">Manage Pages</a></p>';
            echo '</div>';
            delete_transient('dashboard_page_created');
        }
        
        // PMPro dependency notice
        if (!function_exists('pmpro_hasMembershipLevel')) {
            echo '<div class="notice notice-warning">';
            echo '<p><strong>Dashboard Plugin:</strong> For full functionality, please install and activate the Paid Memberships Pro plugin.</p>';
            echo '</div>';
        }
        
        // Template missing notice
        if ($this->dashboard_page_id) {
            $template_path = plugin_dir_path(__DIR__) . 'templates/dashboard-template.php';
            if (!file_exists($template_path)) {
                echo '<div class="notice notice-warning">';
                echo '<p><strong>Dashboard Plugin:</strong> Dashboard template file is missing. Some features may not work correctly.</p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Delete dashboard page (for cleanup)
     */
    public function delete_dashboard_page() {
        if ($this->dashboard_page_id) {
            wp_delete_post($this->dashboard_page_id, true);
            delete_option('dashboard_page_id');
        }
    }
    
    /**
     * Update dashboard page settings
     */
    public function update_dashboard_settings($settings) {
        $allowed_settings = [
            'dashboard_redirect_after_login',
            'dashboard_set_as_homepage',
            'dashboard_required_membership_levels',
            'dashboard_brand_name'
        ];
        
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowed_settings)) {
                update_option($key, $this->security->sanitize_input($value));
            }
        }
        
        return true;
    }
    
    /**
     * Get dashboard settings
     */
    public function get_dashboard_settings() {
        $defaults = [
            'redirect_after_login' => 1,
            'set_as_homepage' => 0,
            'allowed_user_roles' => ['subscriber', 'customer'],
            'theme' => 'gohighlevel',
            'enable_real_time' => 1
        ];
        
        $settings = get_option('dashboard_settings', $defaults);
        
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * PMPro integration for redirect handling v1.0.6
     * 
     * Integrates with PMPro's specific redirect filter to override default account page redirect
     * This runs after PMPro determines if user is a member but before final redirect
     * 
     * @since 1.0.6
     * @param string $redirect_to URL to redirect to
     * @param string $requested_redirect_to Originally requested redirect
     * @param WP_User $user User object
     * @return string Final redirect URL
     */
    public function pmpro_redirect_integration($redirect_to, $requested_redirect_to, $user) {
        // Ensure we have a valid user object
        if (!$user || !isset($user->ID)) {
            return $redirect_to;
        }
        
        // Don't redirect admins
        if (user_can($user, 'manage_options') || user_can($user, 'edit_posts')) {
            return $redirect_to;
        }
        
        // Don't redirect if heading to admin area
        if (is_admin() || strpos($redirect_to, '/wp-admin/') !== false) {
            return $redirect_to;
        }
        
        // Check if user has access to dashboard
        if (!$this->security->user_can_access_dashboard($user->ID)) {
            return $redirect_to;
        }
        
        // Check if dashboard redirect is enabled
        if (!get_option('dashboard_redirect_after_login', 1)) {
            return $redirect_to;
        }
        
        // Get dashboard URL
        $dashboard_url = $this->get_dashboard_url();
        
        if (!$dashboard_url) {
            return $redirect_to;
        }
        
        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Redirect to dashboard handled by admin menu class
        }
        
        return esc_url($dashboard_url);
    }
    
    /**
     * Override PMPro login redirect decision v1.0.8
     * 
     * This hooks into the pmpro_login_redirect filter to completely override PMPro's redirect logic
     * 
     * @since 1.0.8
     * @param bool $do_redirect Whether PMPro should redirect
     * @return bool Modified redirect decision
     */
    public function pmpro_login_redirect_override($do_redirect) {
        // Don't interfere with admin users
        if (current_user_can('manage_options') || current_user_can('edit_posts')) {
            return $do_redirect;
        }
        
        // Don't interfere if dashboard redirect is disabled
        if (!get_option('dashboard_redirect_after_login', 1)) {
            return $do_redirect;
        }
        
        // Check if current user can access dashboard
        if (!$this->security->user_can_access_dashboard()) {
            return $do_redirect;
        }
        
        // Override PMPro's redirect decision - let our admin menu class handle redirects
        return false; // Tell PMPro not to redirect, we'll handle it
    }
    
    /**
     * Handle wp_login action for direct redirect v1.0.8
     * 
     * This is a fallback that triggers on the wp_login action to ensure
     * PMPro members get redirected to dashboard even if other methods fail
     * 
     * @since 1.0.8
     * @param string $user_login Username
     * @param WP_User $user User object
     */
    public function handle_wp_login($user_login, $user) {
        // Don't interfere with admin users
        if (user_can($user, 'manage_options') || user_can($user, 'edit_posts')) {
            return;
        }
        
        // Don't interfere if dashboard redirect is disabled
        if (!get_option('dashboard_redirect_after_login', 1)) {
            return;
        }
        
        // Check if user can access dashboard
        if (!$this->security->user_can_access_dashboard($user->ID)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // User access handled by admin menu class
            }
            return;
        }
        
        // Get dashboard URL
        $dashboard_url = $this->get_dashboard_url();
        
        if (!$dashboard_url) {
            return;
        }
        
        // Store redirect in session for template_redirect fallback
        if (!session_id()) {
            session_start();
        }
        $_SESSION['dashboard_pending_redirect'] = $dashboard_url;
    }
    
    /**
     * Fallback redirect check using template_redirect v1.0.8
     * 
     * DISABLED: Newer admin menu class handles redirects more comprehensively
     * 
     * @since 1.0.8
     */
    public function fallback_redirect_check() {
        // DISABLED: Let the Dashboard Admin Menu class handle all redirects
        // This function was causing conflicts with the newer, more comprehensive
        // redirect system that supports different URLs for different user types
        
        return;
    }
    

} 