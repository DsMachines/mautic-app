<?php
namespace Dashboard\Security;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Security_Manager {
    private $rate_limits = [];
    private $nonce_actions = [
        'dashboard_load_content' => 'dashboard_load_content_nonce',
        'save_canvas' => 'save_canvas_nonce',
        'execute_container' => 'execute_container_nonce'
    ];
    
    public function __construct() {
        add_action('init', [$this, 'init_security_measures']);
    }
    
    public function init_security_measures() {
        // Rate limiting for AJAX actions
        add_action('wp_ajax_load_dashboard_content', [$this, 'rate_limit_check'], 1);
        add_action('wp_ajax_save_canvas', [$this, 'rate_limit_check'], 1);
        add_action('wp_ajax_execute_container', [$this, 'rate_limit_check'], 1);
        
        // Security headers for dashboard pages
        add_action('send_headers', [$this, 'add_security_headers']);
    }
    
    /**
     * Check if user can access dashboard
     */
    public function user_can_access_dashboard($user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        
        if (!$user_id) {
            return false;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Check PMPro membership (if available)
        if (function_exists('pmpro_hasMembershipLevel')) {
            $required_levels = get_option('dashboard_required_membership_levels', []);
            
            // If specific levels are required, check them
            if (!empty($required_levels)) {
                return pmpro_hasMembershipLevel($required_levels, $user_id);
            }
            
            // If no specific levels required, check if user has any active membership
            if (pmpro_hasMembershipLevel(null, $user_id)) {
                return true;
            }
        }
        
        // Fallback: check for basic capability
        return user_can($user_id, 'read');
    }
    
    /**
     * Verify AJAX nonce
     */
    public function verify_ajax_nonce($action) {
        if (!isset($this->nonce_actions[$action])) {
            wp_die('Invalid action');
        }
        
        $nonce_field = $this->nonce_actions[$action];
        
        if (!wp_verify_nonce($_POST['nonce'] ?? '', $nonce_field)) {
            wp_die('Security check failed');
        }
        
        return true;
    }
    
    /**
     * Rate limiting for AJAX requests
     */
    public function rate_limit_check() {
        $user_id = get_current_user_id();
        $action = $_POST['action'] ?? '';
        $current_time = time();
        
        $rate_limit_key = $user_id . '_' . $action;
        
        if (!isset($this->rate_limits[$rate_limit_key])) {
            $this->rate_limits[$rate_limit_key] = [];
        }
        
        // Clean old entries (older than 1 minute)
        $this->rate_limits[$rate_limit_key] = array_filter(
            $this->rate_limits[$rate_limit_key],
            function($timestamp) use ($current_time) {
                return ($current_time - $timestamp) < 60;
            }
        );
        
        // Check rate limit (max 30 requests per minute)
        if (count($this->rate_limits[$rate_limit_key]) >= 30) {
            wp_die('Rate limit exceeded. Please try again later.', 'Rate Limit', [
                'response' => 429
            ]);
        }
        
        // Add current request
        $this->rate_limits[$rate_limit_key][] = $current_time;
    }
    
    /**
     * Add security headers for dashboard pages v1.0.7
     * 
     * Fixed Font Awesome CSP violation by adding font-src directive
     * Now allows data URIs for Font Awesome icons and CDN fonts
     * 
     * @since 1.0.7 - Fixed CSP font loading
     */
    public function add_security_headers() {
        if (!$this->is_dashboard_page()) {
            return;
        }
        
        // Content Security Policy - Fixed Font Awesome data URI support
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
               "font-src 'self' data: https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self'";
               
        $supabase_url = get_option('dashboard_supabase_url');
        if ($supabase_url) {
            $csp .= " " . esc_url($supabase_url);
        }
        
        header("Content-Security-Policy: " . $csp);
        
        // Additional security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * Check if current page is a dashboard page
     */
    private function is_dashboard_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check if current page is the dashboard page
        $dashboard_page_id = get_option('dashboard_page_id');
        if ($dashboard_page_id && $post->ID == $dashboard_page_id) {
            return true;
        }
        
        // Check if page contains dashboard shortcode
        return has_shortcode($post->post_content, 'dashboard') || 
               has_shortcode($post->post_content, 'pmpro_dashboard');
    }
    
    /**
     * Sanitize input based on type
     */
    public function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url_raw($input);
            case 'textarea':
                return sanitize_textarea_field($input);
            case 'int':
                return intval($input);
            case 'json':
                $decoded = json_decode($input, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : false;
            default:
                return sanitize_text_field($input);
        }
    }
    
    /**
     * Escape output based on context
     */
    public function escape_output($output, $context = 'html') {
        switch ($context) {
            case 'attr':
                return esc_attr($output);
            case 'url':
                return esc_url($output);
            case 'js':
                return esc_js($output);
            case 'textarea':
                return esc_textarea($output);
            default:
                return esc_html($output);
        }
    }
} 