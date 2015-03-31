<?php
/*
Plugin Name: WP GIF Player
Description:  An easy to use GIF Player for Wordpress
Version: 0.8
Author: Stefanie Stoppel @ psmedia GmbH
Author URI: http://p-s-media.de/
*/

/*
WP GIF Player, an easy to use GIF Player for Wordpress
Copyright (C) 2015  Stefanie Stoppel @ psmedia GmbH (http://p-s-media.de/kontakt)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class WP_GIF_Player{

    private $options;

    //Constructor, adds all actions and filters
    function __construct(){
        add_action( 'init', array( $this, 'register_shortcodes') );
        add_action('after_setup_theme', array($this, 'localisation_setup'));
        //admin stuff
        if( is_admin() ){
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action( 'admin_init', array($this, 'register_plugin_settings' ));
            add_action( 'add_attachment', array($this, 'on_attachment_save'));
            add_action( 'save_post', array( $this, 'on_post_save' ) );
            add_action( 'media_buttons', array($this, 'add_gif_media_button'), 15); //add media button to editor
            add_action( 'wp_enqueue_media', array($this, 'add_gif_media_button_js'));
        }else{
            add_action( 'wp_enqueue_scripts', array( $this, 'ad_play_functionality' ) );
            add_action( 'init', array($this, 'ad_styles' ) );
        }
    }

    function localisation_setup(){
        load_plugin_textdomain('WPGP', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /*
     * Plugin styles
     */
    function ad_styles(){
        if( !is_admin() ){
            wp_register_style( 'style_main', plugins_url('/style.css', __FILE__), array(), filemtime( dirname(__FILE__) . '/style.css' ) ); //register style with versioning
            wp_enqueue_style( 'style_main' );
        }
    }

    /*
     *     Register and enqueue the js scripts for frontend functionality
     */
    function ad_play_functionality(){
        if( !is_admin() ){
            wp_register_script( 'play_gifs', plugins_url('/js/play_gif.js', __FILE__), array('jquery'), filemtime( dirname(__FILE__) . '/js/play_gif.js') , true);
            wp_enqueue_script( 'play_gifs' );
            wp_register_script( 'spin', plugins_url( 'inc/spin.js', __FILE__ ), array('jquery'), '1.0', true);
            wp_enqueue_script( 'spin' );
            wp_register_script( 'spinjQuery', plugins_url( 'inc/jquery.spin.js', __FILE__ ), array('jquery'), '1.0', true);
            wp_enqueue_script( 'spinjQuery' );
        }
    }

    /*
     * Adds the "Add GIF" button on top of the editor panel.
     */
    function add_gif_media_button_js(){
        if(is_admin()){
            wp_register_script('gif_media_button', plugins_url('/js/gif_media_button.js', __FILE__), array('jquery'), filemtime( dirname(__FILE__) . '/js/gif_media_button.js' ), true);
            wp_enqueue_script('gif_media_button');
        }
    }

    /**
     * Adds "Add GIF" Button in WP editor.
     */
    function add_gif_media_button(){
        echo '<a href="#" id="wpgp-insert-gif" class="button">' . __('Add GIF', 'WPGP') . '</a>';
    }


    /**
     * Shortcode function. Adds HTML element wrappers for the gif, still and play button.
     *
     * @param $atts: ID of the Gif to be inserted (attachment id)
     * @return string: HTML output
     */
    function shortcodes($atts){
        extract(shortcode_atts(array(
            'gif_id' => '',
            'width' => '',
        ), $atts));

        if(is_numeric($width)){
            $width = 'wpgp-width'.$width;
        }

        $output = '';
        if($gif_id != null && !empty($gif_id) ){
            if(get_post_mime_type($gif_id) === "image/gif"){
                $gif_array = wp_get_attachment_image_src($gif_id, 'full');
                $gif_attach = '';
                if(is_array($gif_array)){
                    $gif_attach = $gif_array[0];
                }
                $still_attach = preg_replace('/\.gif$/', '_still_tmp.jpeg', $gif_attach);
                $output = '<div class="gif_wrap ' . $width . '">
                        <span class="empty_span ' . $width . '"></span>
                        <span class="play_gif ' . $width . '">GIF</span>
                        <img src="' . $still_attach . '" class="_showing frame no-lazy" />
                   </div>
                   <img src="' . $still_attach . '" class="_hidden no-lazy" alt="bla" style="display:none;" /><br>';
            }
        }else{
            $output = '<p>' . _e('Sorry, but we couldn\'t find a GIF with this ID!', 'WPGP') . '</p>';
        }
        return $output;
    }


    /**
     * Register WP Gif Player Shortcode.
     * Calls shortcodes() function.
     */
    function register_shortcodes(){
        add_shortcode('WPGP', array($this, 'shortcodes') );
    }


    /**
     * Get the first frame's (still image) url by passing the path in
     *
     * @param $still_path
     * @return mixed
     */
    function get_still_url_by_path($still_path){
        return str_replace( ABSPATH, home_url(), $still_path);
    }

    /**
     * When a Gif is inserted to the media library, get the first frame and save that to media library too.
     *
     * @param $attach_id
     */
    function on_attachment_save($attach_id){
        $mime_type = get_post_mime_type( $attach_id );
        if( isset($mime_type) && $mime_type != '' ) {
            if ($mime_type == 'image/gif') { //ONLY do this for gifs
                //get gif url and its' parent post id
                $gif_src = wp_get_attachment_image_src($attach_id);
                $gif_url = $gif_src[0];
                $parent_id = wp_get_post_parent_id($attach_id);

                //extract the first frame, return the still image's path
                $still_path = $this->extract_first_frame( $gif_url );
                $still_url = $this->get_still_url_by_path($still_path);
                $still_filetype = wp_check_filetype($still_path);

                $attachment = array(
                    'guid'           => $still_url,
                    'post_mime_type' => $still_filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $still_path ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $attach_id = wp_insert_attachment( $attachment, $still_path, $parent_id );
                if($attach_id != 0){
                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    // Generate the metadata for the attachment, and update the database record.
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $still_path );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                }
            }
        }
    }

    /**
     * Called when post is saved/updated.
     * Set attached gif (Junq) or the first frame of the gif (else) as thumbnail for this post.
     *
     * @param $post_id
     */
    function on_post_save( $post_id ){
        // Get "set still as thumbnail" option
        $this->options = get_option( 'set_still_as_featured' );
        $set_still_thumb = false;
        $set_gif_thumb = false;
        if(isset($this->options['featured_check']) && $this->options['featured_check'] === "1"){ //set first frame of first gif as thumbnail
            $set_still_thumb = true; //option was selected by user
        }else if(isset($this->options['featured_check']) && $this->options['featured_check'] === "2"){ //set first gif as thumbnail
            $set_gif_thumb = true;
        }else if(isset($this->options['featured_check']) && $this->options['featured_check'] === "0"){ //no automatic thumbnail
            $set_still_thumb = false;
            $set_gif_thumb = false;
        }

        $first_frame = null;
        //only set thumbnail if user has enabled corresponding setting
        if($set_still_thumb || $set_gif_thumb){
            if(!has_post_thumbnail($post_id)){
                $args = array(
                    'post_parent' => $post_id,
                    'post_type'   => 'attachment',
                    'posts_per_page' => -1,
                    'post_status' => 'image',
                    'orderby' => 'ID',
                    'order' => 'ASC'
                );
                $post_attach_array = get_posts($args); // get all images attached to this post

                foreach($post_attach_array as $attachment){
                    if(($set_still_thumb && $attachment->post_mime_type == "image/jpeg") || ($set_gif_thumb && $attachment->post_mime_type == "image/gif")){
                        $id = set_post_thumbnail($post_id, $attachment->ID);
                        if($id != false){
                            $first_frame = $attachment->guid;
                        }
                    }
                    if($first_frame != null) break;
                }
            }
        }
        //save/update meta information about the still image
        if($first_frame != null){
            $first_frame = preg_replace( '/\.gif$/', '_still_tmp.jpeg',  $first_frame);
            update_post_meta($post_id, '_first_frame', $first_frame);
            //automatically set category = 'GIF' for this post, if there is a category with that name
            $category_names = array('GIF', 'Gif', 'gif');
            foreach($category_names as $cn){
                if(get_cat_ID($cn) != 0){
                    $cat_id = get_cat_ID($cn);
                    wp_set_post_categories( $post_id, $cat_id, true );
                }
            }
            //TODO: maybe as setting as well?
            set_post_format($post_id, 'image');
        }
    }

    /**
     * Extract the first frame of the gif as a jpeg file and save it to the media library.
     * Returns the URL of this still image.
     */
    function extract_first_frame( $img_url ){
        //create a new file path by adding _still(.gif) to the current file path
        $new_still = preg_replace('/\.gif$/', '_still_tmp.jpeg', $img_url);
        $new_still_path = str_replace( home_url(), ABSPATH, $new_still); //hier kann auch ein Array übergeben werden
        //Extract the first frame of the gif as a jpeg file and save it to the media library.
        imagejpeg(imagecreatefromgif($img_url), $new_still_path);

        return $new_still_path; //return path to still first frame
    }


    /*
    * Add menu for this plugin under "Settings -> WP Gif Player"
    */
    function add_options_menu(){
        add_options_page( 'WP Gif Player Options', 'WP Gif Player', 'manage_options', 'wp-gif-player-menu', array($this, 'options_output') );
    }

    /*
     * Output of settings page
     */
    function options_output(){
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        // Set class property
        $this->options = get_option( 'set_still_as_featured' );

        echo '<div class="wrap">
                <h2>' . _e('WP Gif Player Settings', 'WPGP' ). '</h2>
                <form method="post" action="options.php"> ';
        settings_fields( 'featured_still' );
        do_settings_sections( 'wp-gif-player-menu' );
        submit_button();
        echo '</form>
             </div>';
    }

    /*
     * Plugin settings sections and fields
     */
    function register_plugin_settings() {
        register_setting(
            'featured_still', //option group
            'set_still_as_featured'); //option name

        add_settings_section(
            'featured_section', // ID
            __('Thumbnail Settings', 'WPGP'), // Title
            array( $this, 'print_section_info' ), // Callback
            'wp-gif-player-menu' // Page
        );

        add_settings_field(
            'enable_featured', //ID
            __('Set post thumbnail automatically?', 'WPGP'), //Title
            array($this, 'enable_featured_callback'), //Callback function (prints checkbox etc)
            'wp-gif-player-menu', //settings page slug
            'featured_section'
        );

    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        _e('Choose your settings below:', 'WPGP');
    }

    /*
     * Callback for setting thumbnails automatically
     */
    function enable_featured_callback(){
        //Radio button for no featured image ( = standard selection)
        $html = '<input type="radio" id="featured_none_check" name="set_still_as_featured[featured_check]" value="0"';
        if($this->options == ""){
            $html .= 'checked />';
        }else if(isset( $this->options['featured_check']) && $this->options['featured_check'] === "0"){
            $html .= ' checked />';
        }
        $html .= '<label for="featured_still_check">' . __('Don\'t set a post thumbnail', 'WPGP') . '</label>';

        //Radio button for still as featured
        $html .= '<br><input type="radio" id="featured_still_check" name="set_still_as_featured[featured_check]" value="1"';
        if($this->options == ""){
            $html .= '/>';
        }else if(isset( $this->options['featured_check']) && $this->options['featured_check'] === "1"){
            $html .= ' checked />';
        }
        $html .= '<label for="featured_still_check">' . __('Set first frame of first GIF as Thumbnail', 'WPGP') . '</label>';

        //Radio button for gif as featured
        $html .= '<br><input type="radio" id="featured_gif_check" name="set_still_as_featured[featured_check]" value="2"';
        if($this->options == ""){
            $html .= '/>';
        }else if(isset( $this->options['featured_check']) && $this->options['featured_check'] === "2"){
            $html .= ' checked />';
        }
        $html .= '<label for="featured_gif_check">' . __('Set first GIF as Thumbnail', 'WPGP') . '</label>';

        echo $html;
    }



    /************************************************************************************************************/
    /************************************************** UNUSED **************************************************/
    /************************************************************************************************************/

    /**
     * Check whether there is an attachment with 'guid' = $image_url in the wp_posts table.
     * If so: return its' ID.
     * Else: return null.
     *
     * @param $image_url
     * @return mixed
     */
    function get_attachment_id_from_src ($image_url) {
        global $wpdb;
        $query = "SELECT `id` FROM {$wpdb->posts} WHERE guid = %s";
        $id = $wpdb->get_var($wpdb->prepare($query, $image_url));
        return $id;
    }

    /**
     * Automatically set an image that is attached to the post as featured image.
     * Can be a gif file.
     *
     * @param $post_id
     */
    function autoset_featured($post_id) {
        $already_has_thumb = has_post_thumbnail($post_id);
        if (!$already_has_thumb)  {
            $attached_image = get_children( "post_parent=$post_id&post_type=attachment&post_mime_type=image&numberposts=1" );
            if ($attached_image) {
                foreach ($attached_image as $attachment_id => $attachment) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
        }
    }

    /**
     * If the posts thumb is a gif, set the first frame of the gif (jpeg) as new featured image.
     *
     * @param $post_id
     * @param $img_url
     * @return bool
     */
    function autoset_featured_still($post_id, $img_url){
        $res = false;
        //Check whether attachment with this url exists already in db
        $attachment_id = $this->get_attachment_id_from_src($img_url);
        if($attachment_id == null){ //attachment doesnt exist
            $img_path = str_replace( home_url(), rtrim(ABSPATH, "/"), $img_url);
            $filetype = wp_check_filetype($img_path);
            $attachment = array(
                'guid'           => $img_url,
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $img_path ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            //insert attachment
            $attach_id = wp_insert_attachment( $attachment, $img_path, $post_id );
            if($attach_id != 0){
                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $img_path );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                $meta_id = set_post_thumbnail($post_id, $attach_id);
                if($meta_id != false){
                    $res = true;
                }
            }
        }else{ //attachment exists
            $meta_id = set_post_thumbnail($post_id, $attachment_id);
            if($meta_id != false){
                $res = true;
            }
        }
        return $res;
    }

}
new WP_GIF_Player();