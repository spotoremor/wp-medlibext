<?php
/**
 * DC Image Library Core Class
 * 
 * @since      1.0.0
 * @package    DC Image Library
 * @author     Nowcom
 */

class DC_Image_Library_Core{
   private $plugin_name;
   private $version;
    
    public function __construct() {
        $this->plugin_name = 'DC Image LIbrary Extension';
        $this->version = '1.0.0';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
    }
    
    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/loader_class.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin_class.php';

        $this->loader = new DC_Image_Library_Loader();
    }
    
    private function define_admin_hooks() {

        $plugin_admin = new DC_Image_Library_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('media_upload_tabs', $plugin_admin, 'upload_tab');
        $this->loader->add_action('media_upload_dcimglibtab', $plugin_admin, 'upload_form');
    }
    
    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
    
    public static function activate() {
    // code to run on plugin activation    
    }
    
    public static function deactivate() {
    // code to run on plugin activation (not plugin uninstall)
    }
}