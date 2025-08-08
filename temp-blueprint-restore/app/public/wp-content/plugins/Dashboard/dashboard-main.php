<?php
/**
 * Plugin Name: Dashboard
 * Description: CPaaS Dashboard.
 * Version: 1.1.9
 * Author: ExpertWriter
 * Requires PHP: 8.3
 * Requires at least: 6.8
 * Network: false
 * Text Domain: dashboard-plugin
 * License: GPL v2 or later
 */

namespace Dashboard;

use Dashboard\Core\Dashboard_Page_Manager;
use Dashboard\Security\Security_Manager;
use Dashboard\Admin\Dashboard_Admin_Menu;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Information v1.1.9
 */
define('DASHBOARD_VERSION', '1.1.9');
define('DASHBOARD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DASHBOARD_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main Dashboard Plugin Class
 * 
 * Handles core plugin functionality and coordinates between modules
 * Following .cursorrules security and performance standards
 */
class Dashboard_Plugin {
    private static ?self $instance = null;
    private readonly string $version;
    private $page_manager;
    private $security;
    private $admin_menu;
    
    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->version = DASHBOARD_VERSION;
        $this->init();
    }
    
    private function init() {
        $this->load_dependencies();
        $this->init_components();
        $this->define_hooks();
    }
    
    private function load_dependencies() {
        require_once DASHBOARD_PLUGIN_PATH . 'includes/class-dashboard-page-manager.php';
        require_once DASHBOARD_PLUGIN_PATH . 'includes/class-security-manager.php';
        require_once DASHBOARD_PLUGIN_PATH . 'includes/class-dashboard-admin-menu.php';
    }
    
    private function init_components() {
        $this->page_manager = new Dashboard_Page_Manager($this->version);
        $this->security = new Security_Manager();
        $this->admin_menu = new Dashboard_Admin_Menu($this->version);
        
        // Set dependencies between components
        $this->admin_menu->set_dependencies($this->security, $this->page_manager);
    }
    
    private function define_hooks() {
        // Plugin activation and deactivation only
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_database_tables();
        $this->set_default_options();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables for future use
     */
    private function create_database_tables() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        $max_index_length = 191;
        
        // Dashboard containers table (for Point 2)
        $sql_containers = "CREATE TABLE {$wpdb->prefix}dashboard_containers (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL DEFAULT '0',
            container_name varchar(255) NOT NULL,
            shortcode_content text NOT NULL,
            menu_parent varchar(100) NOT NULL DEFAULT '',
            menu_position int(11) NOT NULL DEFAULT '0',
            is_active tinyint(1) NOT NULL DEFAULT '1',
            required_capability varchar(100) NOT NULL DEFAULT 'read',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY menu_parent (menu_parent),
            KEY is_active (is_active),
            KEY container_name (container_name({$max_index_length}))
        ) $charset_collate;";
        
        // Dashboard canvas table (for Point 2)
        $sql_canvas = "CREATE TABLE {$wpdb->prefix}dashboard_canvas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            canvas_name varchar(255) NOT NULL DEFAULT 'Default Canvas',
            canvas_data longtext NOT NULL,
            is_default tinyint(1) NOT NULL DEFAULT '0',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_default (is_default),
            KEY canvas_name (canvas_name({$max_index_length}))
        ) $charset_collate;";
        
        dbDelta($sql_containers);
        dbDelta($sql_canvas);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        add_option('dashboard_version', $this->version);
        add_option('dashboard_brand_name', 'Dashboard');
        
        // PMPro membership settings
        add_option('dashboard_required_membership_levels', []);
        add_option('dashboard_redirect_after_login', 1);
        add_option('dashboard_set_as_homepage', 0);
        
        // Performance settings for future use
        add_option('dashboard_realtime_enabled', 1);
        add_option('dashboard_cache_enabled', 1);
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Check if dashboard page exists
     */
    public function dashboard_page_exists() {
        return $this->page_manager && method_exists($this->page_manager, 'get_dashboard_url');
    }
}

// Initialize the plugin
function init_dashboard_plugin() {
    Dashboard_Plugin::get_instance();
}

// Hook into WordPress
add_action('plugins_loaded', 'Dashboard\\init_dashboard_plugin');