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
 //       add_filter('wp_get_attachment_url', array($this, 'wp_get_attachment_url'), 10, 2);
        wp_iframe(array($this, 'media_show_upload_form'));
    }
    public function get_attached_file( $file, $attachment_id ){
        if(strpos($file, '/wp-content/plugins/dc_image_library/data/images/') !== FALSE){
            $file = get_post_meta( $attachment_id, '_wp_attached_file', true );
        }
        
        return $file;
    }
    
    public function wp_get_attachment_url($url, $post_id){
    if(strpos($url, '/wp-content/plugins/dc_image_library/data/images/') !== FALSE){
        $url = get_post_meta( $post_id, '_wp_attached_file', true );
    }
        return $url;
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
        
        // Check that the nonce is valid, and the user can edit this post.
        if ( 
                isset( $_POST['my_image_upload_nonce'], $_POST['post_id'] ) 
                && wp_verify_nonce( $_POST['my_image_upload_nonce'], 'my_image_upload' )
                && current_user_can( 'edit_post', $_POST['post_id'] )
        ) {
            // The nonce was valid and the user has the capabilities, it is safe to continue.

            // $filename should be the path to a file in the upload directory.
            $filename = $_POST['image_filename'];

            // The ID of the post this attachment is for.
            $parent_post_id = $_POST['post_id'];

            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filetype = wp_check_filetype( basename( $filename ), null );

            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();
            // Prepare an array of post data for the attachment.
            $attachment = array(
                    'guid'           => home_url('/' . $filename ), 
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
            );

            // Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, home_url('/' . $filename ), $parent_post_id );

            
            // These files need to be included as dependencies when on the front end.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
//            require_once( ABSPATH . 'wp-admin/includes/file.php' );
//            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            // Let WordPress handle the upload.
            // Remember, 'my_image_upload' is the name of our file input in our form above.
            //$attachment_id = media_handle_upload( 'my_image_upload', $_POST['post_id'] );

            if ( is_wp_error( $attach_id ) ) {
                    // There was an error uploading the image.
            } else {
                    // The image was uploaded successfully!
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, home_url('/' . $filename ) );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                set_post_thumbnail( $parent_post_id, $attach_id );
                
            }

        } else {

            // The security check failed, maybe show the user an error.
        }

        $image_types = self::get_image_types();
        $images_meta = self::get_images_by_type();
        ?>

        <div>
            <div>
                <div>
                    <label for="media-attachment-filters">Filter by type</label>
                    <select id="media-attachment-filters">
                        <option value="all">All images</option>
                        <?php foreach ($image_types as $key => $value): ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <ul id="media-list">
                <?php $tabindex = 0 ?>
                <?php foreach ($images_meta as $file_id => $image): ?>
                    <?php $tabindex++ ?>
                    <li tabindex="<?php echo $tabindex; ?>" aria-label="<?php echo sanitize_title($image['title']); ?>" data-types="all <?php echo $image['categories']; ?>">
                        <div>
                            <div>
                                <div>
                                    <a href="javascript:DCInsertImage('<?php echo $image['url']; ?>');">
                                    <img src="<?php echo $image['url']; ?>" draggable="false" alt="" width="128">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <form id="dc_imalibext_form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="image_filename" id="image_filename"/>
            <input type="hidden" name="post_id" id="post_id" value="<?php echo $_GET['post_id']; ?>" />
            <?php wp_nonce_field( 'my_image_upload', 'my_image_upload_nonce' ); ?>
            <input id="dc_imalibext_submit" name="dc_imalibext_submit" type="submit" value="Upload" />
        </form>
        </div>
        <?php
    }

    static function xmedia_show_upload_form() {
        media_upload_header();
        //wp_enqueue_media();
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
                                    <a href="javascript:DCInsertImage('<?php echo $image['url']; ?>');">
                                    <img src="<?php echo $image['url']; ?>" draggable="false" alt="" width="128">
                                    </a>
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
