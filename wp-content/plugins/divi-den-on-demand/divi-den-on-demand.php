<?php
/*
Plugin Name: Divi Den on Demand
Plugin URI:  https://seku.re/ddd
Description: Get easy access to tons of Free and Premium Divi Page Layouts to speed up your work flow. Find great designs and build awesome pages. Search by keyword or browse by topic, page type, product or Divi module. Use the preview button to see live and working demos of the layouts before you import them. We look forward to your feedback, so we can make it even more awesome.

Version:     1.4.6
Author:      Divi Den
Author URI:  https://seku.re/divi-den
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//======================================================================
// Load Persist Admin notices Dismissal
//======================================================================

if ( ! class_exists( 'PAnD' ) ) {
require_once( plugin_dir_path( __FILE__ ) . '/include/persist-admin-notices-dismissal.php' );
add_action( 'admin_init', array( 'PAnD', 'init' ) );
}

//======================================================================
//  Load the API Key library if it is not already loaded. Must be placed in the root plugin file.
//======================================================================

if ( ! class_exists( 'ddd_AM_License_Menu' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'ddd-am-license-menu.php' );
    ddd_AM_License_Menu::instance( __FILE__, 'Divi Den on Demand', '1.4.6', 'plugin', 'https://divi-den.com/' );
}

//======================================================================
// CHECK IF DIVI THEME INSTALLED
//======================================================================

function ddd_assistant_not_installed_admin_notice__error() {
	$class = 'notice notice-error is-dismissible';
	$message = 'Action Required: The Divi Theme is not installed. You must install the Divi Theme for the Divi Den on Demand to work. If you do not already have it, <a href="https://seku.re/get-divi" target="_blank">Get it here</a>';

	printf( '<div data-dismissible="disable-ddd-status-warning-notice-forever" class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
}

$divi_theme = wp_get_theme( 'Divi' );
if ( !($divi_theme->exists()) ) add_action( 'admin_notices', 'ddd_assistant_not_installed_admin_notice__error' );


//======================================================================
// ADD ADMIN SCRIPTS
//======================================================================

add_action( 'admin_enqueue_scripts', 'ddd_enqueue_admin_js' );

function ddd_enqueue_admin_js( $hook_suffix ) {
    wp_enqueue_script( 'ddd_assistant-clipboard', plugins_url('js/clipboard.min.js', __FILE__ ), array(), "1.4.0", 'false');
    wp_enqueue_script( 'ddd_assistant-admin', plugins_url('js/ddd-admin.js', __FILE__ ), array(), "1.4.0", 'false');
    $ddd_path_to_plugin = plugin_dir_url(__FILE__);
    wp_localize_script( 'ddd_assistant-admin', 'ddd_path_to_plugin', $ddd_path_to_plugin );
}

//======================================================================
// ADD ADMIN CSS
//======================================================================

add_action( 'admin_enqueue_scripts', 'ddd_enqueue_admin_css' );
add_action('et_builder_ready', 'ddd_enqueue_admin_css');

function ddd_enqueue_admin_css() {
    wp_register_style( 'ddd_assistant-admin-css', plugins_url('css/ddd-admin.css', __FILE__ ), array(), '1.4.0', 'all' );
    wp_enqueue_style( 'ddd_assistant-admin-css' );
}

//======================================================================
// New options for plugin's setting
//======================================================================

function ddd_add_options_on_activate() {

if (!get_option( 'ddd_enable')) add_option( 'ddd_enable', 'enabled' );

}

register_activation_hook( __FILE__, 'ddd_add_options_on_activate' );

//======================================================================
// For ajax get and set options
//======================================================================

function ddd_get_option(){
      echo get_option('ddd_enable');
      die();
}

function ddd_update_option(){
      update_option($_POST['ddd_option'], esc_attr($_POST['ddd_option_val']));
      die();
}

function ddd_get_plugin_activation_state() {
    $plugin_name = esc_attr($_GET['plugin_name']).'_assistant_activated';
    echo get_option( $plugin_name );
    die();
}

function ddd_get_divi_custom_css() {
    echo get_post_meta( $_GET['post_id'], '_et_pb_custom_css', true );
    die();
}

function ddd_update_divi_custom_css() {
    update_post_meta( $_POST['post_id'], '_et_pb_custom_css', $_POST['new_css'] );
    die();
}

//======================================================================
// SAVE TO DIVI LIBRALY
//======================================================================

// if (!function_exists('write_log')) {
//     function write_log ( $log )  {
//         if ( true === WP_DEBUG ) {
//             if ( is_array( $log ) || is_object( $log ) ) {
//                 error_log( print_r( $log, true ) );
//             } else {
//                 error_log( $log );
//             }
//         }
//     }
// }


//======================================================================
// UPLOAD IMAGES
//======================================================================

 function ddd_set_filesystem() {
        global $wp_filesystem;

       // add_filter( 'filesystem_method', array( 'replace_filesystem_method' ) );
        WP_Filesystem();

        return $wp_filesystem;
    }

    function ddd_upload_images( $images ) {
        $filesystem = ddd_set_filesystem();

        foreach ( $images as $key => $image ) {
            $basename    = sanitize_file_name( wp_basename( $image['url'] ) );
            $attachments = get_posts( array(
                'posts_per_page' => -1,
                'post_type'      => 'attachment',
                'meta_key'       => '_wp_attached_file',
                'meta_value'     => pathinfo( $basename, PATHINFO_FILENAME ),
                'meta_compare'   => 'LIKE',
            ) );
            $id = 0;
            $url = '';

            // Avoid duplicates.
            if ( ! is_wp_error( $attachments ) && ! empty( $attachments ) ) {
                foreach ( $attachments as $attachment ) {
                    $attachment_url = wp_get_attachment_url( $attachment->ID );
                    $file           = get_attached_file( $attachment->ID );
                    $filename       = sanitize_file_name( wp_basename( $file ) );

                    // Use existing image only if the content matches.
                    if ( $filesystem->get_contents( $file ) === base64_decode( $image['encoded'] ) ) {
                        $id = isset( $image['id'] ) ? $attachment->ID : 0;
                        $url = $attachment_url;

                        break;
                    }
                }
            }

            // Create new image.
            if ( empty( $url ) ) {
                $temp_file = wp_tempnam();
                $filesystem->put_contents( $temp_file, base64_decode( $image['encoded'] ) );
                $filetype = wp_check_filetype_and_ext( $temp_file, $basename );

                // Avoid further duplicates if the proper_file name match an existing image.
                if ( isset( $filetype['proper_filename'] ) && $filetype['proper_filename'] !== $basename ) {
                    if ( isset( $filename ) && $filename === $filetype['proper_filename'] ) {
                        // Use existing image only if the basenames and content match.
                        if ( $filesystem->get_contents( $file ) === $filesystem->get_contents( $temp_file ) ) {
                            $filesystem->delete( $temp_file );
                            continue;
                        }
                    }
                }

                $file = array(
                    'name'     => $basename,
                    'tmp_name' => $temp_file,
                );
                $upload = media_handle_sideload( $file, 0 );

                if ( ! is_wp_error( $upload ) ) {
                    // Set the replacement as an id if the original image was set as an id (for gallery).
                    $id = isset( $image['id'] ) ? $upload : 0;
                    $url = wp_get_attachment_url( $upload );
                } else {
                    // Make sure the temporary file is removed if media_handle_sideload didn't take care of it.
                    $filesystem->delete( $temp_file );
                }
            }

            // Only declare the replace if a url is set.
            if ( $id > 0 ) {
                $images[$key]['replacement_id'] = $id;
            }

            if ( ! empty( $url ) ) {
                $images[$key]['replacement_url'] = $url;
            }

            unset( $url );
        }

        return $images;
    }

    //======================================================================
    // REPLACE IMAGES URL
    //======================================================================

    function ddd_replace_image_url( $subject, $image ) {
        if ( isset( $image['replacement_id'] ) && isset( $image['id'] ) ) {
            $search      = $image['id'];
            $replacement = $image['replacement_id'];
            $subject     = preg_replace( "/(gallery_ids=.*){$search}(.*\")/", "\${1}{$replacement}\${2}", $subject );
        }

        if ( isset( $image['url'] ) && isset( $image['replacement_url'] ) && $image['url'] !== $image['replacement_url'] ) {
            $search      = $image['url'];
            $replacement = $image['replacement_url'];
            $subject     = str_replace( $search, $replacement, $subject );
        }

        return $subject;
    }

    function ddd_replace_images_urls( $images, $data ) {
        foreach ( $data as $post_id => &$post_data ) {
            foreach ( $images as $image ) {
                if ( is_array( $post_data ) ) {
                    foreach ( $post_data as $post_param => &$param_value ) {
                        if ( ! is_array( $param_value ) ) {
                            $data[ $post_id ][ $post_param ] = ddd_replace_image_url( $param_value, $image );
                        }
                    }
                    unset($param_value);
                } else {
                    $data[ $post_id ] = ddd_replace_image_url( $post_data, $image );
                }
            }
        }
        unset($post_data);

        return $data;
    }

    function ddd_temp_file( $id, $group, $temp_file = false ) {
        $temp_files = get_option( '_et_core_portability_temp_files', array() );

        if ( ! isset( $temp_files[$group] ) ) {
            $temp_files[$group] = array();
        }

        if ( isset( $temp_files[$group][$id] ) && file_exists( $temp_files[$group][$id] ) ) {
            return $temp_files[$group][$id];
        }

        $temp_file = $temp_file ? $temp_file : wp_tempnam();
        $temp_files[$group][$id] = $temp_file;

        update_option( '_et_core_portability_temp_files', $temp_files, false );

        return $temp_file;
    }


    function ddd_maybe_paginate_images( $images, $method, $timestamp ) {
        et_core_nonce_verified_previously();

        /**
         * Filters whether or not images in the file being imported should be paginated.
         *
         * @since 3.0.99
         *
         * @param bool $paginate_images Default `true`.
         */
        $paginate_images = apply_filters( 'et_core_portability_paginate_images', true );

        if ( $paginate_images && count( $images ) > 5 ) {
             $images = $method( $images );
        } else {
            $images = $method( $images );
        }
        return $images;
    }

    function ddd_get_timestamp() {
        et_core_nonce_verified_previously();

        return isset( $_POST['timestamp'] ) && ! empty( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : current_time( 'timestamp' );
    }

    //======================================================================
    // SAVE TO DIVI LIBRALY
    //======================================================================

    function ddd_import_posts($posts)
    {

        //echo 'START: ddd_import_posts';
        global $wpdb;
        session_start();

        if (!function_exists('post_exists')) {
            require_once(ABSPATH . 'wp-admin/includes/post.php');
        }

        $imported = array();

        $posts_raw = $_POST['posts'];


        if (empty($posts_raw)) {
            //echo 'empry posts raw';
            return;
        }


        $posts = str_replace('\\"', '"', $posts_raw);
        $posts = str_replace('\\\\', '\\', $posts);
        $posts = str_replace("\'", "'", $posts);
        $posts = html_entity_decode($posts, ENT_COMPAT, 'UTF-8');
        $posts = json_decode($posts, true);

        if (empty($posts)) {
            return;
        }


       $posts_data = $posts['data'];
            // Upload images and replace current urls.
        if ( isset( $posts['images'] ) ) {
            $posts_images = $posts['images'];
            $timestamp = ddd_get_timestamp();
            $new_images = ddd_maybe_paginate_images( (array) $posts_images, 'ddd_upload_images', $timestamp );
            $posts_data = ddd_replace_images_urls( $new_images, $posts_data);
        }

        foreach ( $posts_data as $old_post_id => $post) {
            if (isset($post['post_status']) && 'auto-draft' === $post['post_status']) {
                continue;
            }

            $post_exists = post_exists($post['post_title']);

            // Make sure the post is published and stop here if the post exists.
            if ($post_exists && get_post_type($post_exists) == $post['post_type']) {
                if ('publish' == get_post_status($post_exists)) {
                    $imported[$post_exists] = $post['post_title'];
                    $_SESSION['ddd_post_id_for_image'] = $post_exists; //echo 'SET $_SESSION: '.$_SESSION['ddd_post_id_for_image'];
                    $time = current_time('mysql');

                    wp_update_post(
                        array (
                            'ID'            => $post_exists, // ID of the post to update
                            'post_date'     => $time,
                            'post_date_gmt' => get_gmt_from_date( $time )
                        )
                    );
                    continue;
                }
            }

            if (isset($post['ID'])) {
                $post['import_id'] = $post['ID'];
                unset($post['ID']);
            }


            $post['post_author'] = (int) get_current_user_id();

            $post['post_date'] = current_time('mysql');

            $post['post_date_gmt'] = current_time('mysql', 1);

            // Insert or update post.
            $post_id = wp_insert_post($post, true);

            if (!$post_id || is_wp_error($post_id)) {
                continue;
            }

            if (!isset($post['terms'])) {
                $post['terms'] = array();
            }

            $post['terms'][] = array(
                'name' => 'Divi Den',
                'slug' => 'divi-den',
                'taxonomy' => 'layout_category',
                'parent' => 0,
                'description' => ''
            );

            // Insert and set terms.
            if (count($post['terms']) > 0) {
                $processed_terms = array();

                foreach ($post['terms'] as $term) {

                    if (empty($term['parent'])) {
                        $parent = 0;
                    } else {
                        $parent = term_exists($term['name'], $term['taxonomy'], $term['parent']);

                        if (is_array($parent)) {
                            $parent = $parent['term_id'];
                        }
                    }

                    if (!$insert = term_exists($term['name'], $term['taxonomy'], $term['parent'])) {
                        $insert = wp_insert_term($term['name'], $term['taxonomy'], array(
                            'slug' => $term['slug'],
                            'description' => $term['description'],
                            'parent' => intval($parent)
                        ));
                    }

                    if (is_array($insert) && !is_wp_error($insert)) {
                        $processed_terms[$term['taxonomy']][] = $term['slug'];
                    }
                }

                // Set post terms.
                foreach ($processed_terms as $taxonomy => $ids) {
                    wp_set_object_terms($post_id, $ids, $taxonomy);
                }
            }

            // Insert or update post meta.
            if (isset($post['post_meta']) && is_array($post['post_meta'])) {
                foreach ($post['post_meta'] as $meta_key => $meta) {

                    $meta_key = sanitize_text_field($meta_key);

                    if (count($meta) < 2) {
                        $meta = wp_kses_post($meta[0]);
                    } else {
                        $meta = array_map('wp_kses_post', $meta);
                    }

                    update_post_meta($post_id, $meta_key, $meta);
                }
            }

            $imported[$post_id] = $post['post_title'];

        }

        if(!empty($post_id) && $post_id !== 0) {
            $_SESSION['ddd_post_id_for_image'] = $post_id; //echo 'SET $_SESSION: '.$_SESSION['ddd_post_id_for_image'];
        }

        return $imported;

        die();
    }


if( is_admin() ) {
  add_action('wp_ajax_ddd_update_option', 'ddd_update_option', 10, 2);
  add_action('wp_ajax_ddd_get_option', 'ddd_get_option', 9, 1);
  add_action('wp_ajax_ddd_get_plugin_activation_state', 'ddd_get_plugin_activation_state', 8, 1);
  add_action('wp_ajax_ddd_get_divi_custom_css', 'ddd_get_divi_custom_css', 7, 1);
  add_action('wp_ajax_ddd_import_posts', 'ddd_import_posts', 5, 1);
}

//======================================================================
// ADD A LINK TO SETTINGS PAGE
//======================================================================
function add_ddd_settings_link($links)
{
    return array_merge($links, array(
        '<a href="' . admin_url('/admin.php?page=divi_den_on_demand_dashboard') . '">' . __('Settings') . '</a>'
    ));
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_ddd_settings_link');



//======================================================================
// AUTOUPDATE
//======================================================================

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://seku.re/ddfreeupd',
	__FILE__,
	'divi-den-on-demand'
);


?>