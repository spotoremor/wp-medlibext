<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    DC Image Library
 * @author     Nowcom
 */
class DC_Image_Library_Admin {

    public static $instance;
    private $plugin_name;
    private $version;
    private static $images_url;
    private static $data_folder;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        self::$images_url = plugins_url('data/images/', dirname(__FILE__));
        self::$data_folder = plugin_dir_path(__FILE__) . '../data/';
        self::$instance = $this;
    }

    public function upload_tab($tabs) {
        $tabs['dcimglibtab'] = __('DC Image Library', 'dc_image_library');
        return $tabs;
    }

    public function upload_form() {
        add_action('admin_print_styles-media-upload-popup', array($this, 'enqueue_styles'));
        add_action('admin_print_scripts-media-upload-popup', array($this, 'enqueue_scripts'));
        wp_iframe(array($this, 'media_show_upload_form'));
    }

    static function get_image_types() {
        $types = file_get_contents(self::$data_folder . 'image_types.json');

        if ($types !== FALSE) {
            $types = json_decode($types, TRUE); // JSON to array
        } else {
            $types = array();
        }
        return $types;
    }

    static function get_images_by_type($type = NULL) {
        $images = file_get_contents(self::$data_folder . 'images_meta.json');
        if ($images !== FALSE) {

            $images = json_decode($images, TRUE); // JSON to array
            if (!is_null($type)) {
                $images = $images[$type];
            }
        } else {
            $images = array();
        }
        return $images;
    }

    static function media_show_upload_form() {
        media_upload_header();
        wp_enqueue_media();
        $image_types = self::get_image_types();
        $images_meta = self::get_images_by_type();
        ?>

        <div class="attachments-browser">
            <div class="media-toolbar">
                <div class="media-toolbar-secondary">
                    <label for="media-attachment-filters" class="screen-reader-text">Filter by type</label>
                    <select id="media-attachment-filters" class="attachment-filters">
                        <option value="all">All images</option>
                        <?php foreach ($image_types as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <ul tabindex="-1" class="attachments" id="media-list">
                <?php $tabindex = 0 ?>
                <?php foreach ($images_meta as $file_id => $image): ?>
                    <?php $tabindex++ ?>
                    <li tabindex="<?php echo $tabindex; ?>" aria-label="<?php echo sanitize_title($image['title']); ?>" class="attachment save-ready hide" data-types="all <?php echo $image['categories']; ?>">
                        <div class="attachment-preview js--select-attachment type-image subtype-jpeg">
                            <div class="thumbnail">
                                <div class="centered">
                                    <img src="<?php echo $image['url']; ?>" draggable="false" alt="" width="128">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="button-link check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text">Deselect</span></button>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>

        <?php
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/dc_image_library_admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/dc_image_library_admin.js', array('jquery'), $this->version, false);
    }

}
