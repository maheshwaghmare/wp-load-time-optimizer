<?php
/**
 * Plugin Name: Load Time Optimizer
 * Description: Improve the website speed.
 * Plugin URI: https://github.com/maheshwaghmare/load-time-optimizer
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.wordpress.com/
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: load-time-optimizer
 *
 * @package Load Time Optimizer
 */

/**
 * Set constants.
 */
define( 'LTO_VER',  '1.0.0' );
define( 'LTO_FILE', __FILE__ );
define( 'LTO_BASE', plugin_basename( LTO_FILE ) );
define( 'LTO_DIR',  plugin_dir_path( LTO_FILE ) );
define( 'LTO_URI',  plugins_url( '/', LTO_FILE ) );

/**
 * Register meta box(es).
 */
function wpdocs_register_meta_boxes() {
    add_meta_box( 'meta-box-id', __( 'My Meta Box', 'textdomain' ), 'wpdocs_my_display_callback', array( 'post', 'page' ) );
}
add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );
 
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function wpdocs_my_display_callback( $post ) {
	$stored = get_post_meta( $post->ID, 'lto_scripts', true );
	// vl( $stored );

	$settings = get_option( 'load_time_optimizer_scripts', array() );

	foreach ($settings as $key => $setting) {
		$checked = in_array($setting, $stored) ? ' checked="checked" ' : '';
		?>
		<p>
			<label>
				<input <?php echo $checked; ?> type="checkbox" name="lto-scripts[]" value="<?php echo $setting; ?>" /><?php echo $setting; ?>
			</label>
		</p>
		<?php
	}
	?>
	<p>Get Latest Scripts <a target="_blank" href="<?php echo get_the_permalink( get_the_ID() ); ?>?scripts" />Get Links</a></p>
	<?php
	// vl( $settings );
}
 
/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function wpdocs_save_meta_box( $post_id ) {
	if( isset( $_POST['lto-scripts'] ) ) {
		update_post_meta( $post_id, 'lto_scripts', $_POST['lto-scripts'] );
	}
}
add_action( 'save_post', 'wpdocs_save_meta_box' );


/**
 * Remove Unwanted Script Page By Page
 */
add_action( 'wp_head', function() {
	if( ! isset( $_GET['scripts'] ) ) {
		return;
	}

	$old_settings = get_option( 'load_time_optimizer_scripts', array() );

	global $wp_scripts;
	$items = (array) $wp_scripts;
	// vl( array_keys( $items['queue'] ) );
	// wp_die();
	// $items = wp_list_pluck( $items['registered'], 'handle' );
	// $items = wp_list_pluck( $items['queue'], 'handle' );
	// $items = array_keys( $items );
	// 
	?>
	<p>Found Below Scripts</p>
	<?php
	$items = $items['queue'];
	// $items = wp_parse_args( $old_settings, $items );
	update_option( 'load_time_optimizer_scripts', $items );
	vl( $items );
	wp_die();
} );

function theme_name_scripts() {

	if( is_singular() ) {
		$stored = get_post_meta( get_the_ID(), 'lto_scripts', true );
		// vl( $stored );
		if( $stored ) {
			foreach ($stored as $key => $script) {
				wp_dequeue_style( $script );
				wp_dequeue_script( $script );
			}
		}
	}
	// wp_deregister_style( 'wp-embed' );
	// wp_deregister_script( 'wp-embed' );

	// wp_dequeue_style( 'copy-the-code' );
	// wp_dequeue_script( 'copy-the-code' );
}
add_action( 'wp_enqueue_scripts', 'theme_name_scripts', 9999 );

/**
 * Disable Emoji
 */
/**
 * Disable the emoji's
 */
function disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}
add_action( 'init', 'disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param    array  $plugins  
 * @return   array             Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}


if ( ! function_exists( 'vl' ) ) :
    /**
     * Replacement for print_r & var_dump.
     *
     * @param mixed $var
     * @param bool $dump. (default: false)
     */
    function vl( $var, $dump = 0 ) {
        ?>

        <style type="text/css">
            .vl_pre {
                text-align: left;
                margin: 30px 15px;
                padding: 1em;
                border: 0px;
                outline: 0px;
                font-size: 14px;
                font-family: monospace;
                vertical-align: baseline;
                max-width: 100%;
                overflow: auto;
                color: rgb(248,248,242);
                direction: ltr;
                word-spacing: normal;
                line-height: 1.5;
                border-radius: 0.3em;
                word-wrap: normal;
                letter-spacing: 0.266667px;
                background: rgb(61,69,75);
            }
        </style>

        <?php
        
        echo "<pre class='vl_pre'><xmp>";
        if ( true == $dump ) {
            var_dump( $var );
        } else {
            if ( is_array( $var ) || is_object( $var ) ) {
                print_r( $var );
            } else {
                echo $var;
            }
        }
        echo "</xmp></pre>";
    }
endif;
