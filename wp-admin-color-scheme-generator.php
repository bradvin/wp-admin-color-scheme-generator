<?php
/**
 * Plugin Name: Admin Color Scheme Generator
 * Description: A plugin to generate WordPress 3.8 admin color schemes
 * Plugin URI: http://themergency.com/generators/admin-color-scheme-generator
 * Author: bradvin
 * Author URI: http://themergency.com
 * Version: 1.0
 * Text Domain: admin-color-scheme-generator
 * License: GPL2
 *
 * Copyright 2013 Brad Vincent
 */

if ( !defined( 'ACSG_PLUGIN_VER' ) ) {
	define('ACSG_PLUGIN_VER', '1.0');
}

class Admin_Color_Scheme_Generator {

	var $scheme_name = '';
	var $scheme_slug = '';

	var $scss_contents = '';

	function __construct() {
		add_action( 'admin_menu', array($this, 'add_menu') );
		add_action( 'template_redirect', array($this, 'generate_color_scheme') );
		add_action( 'admin_init', array($this, 'generate_color_scheme') );

		//do_action('admin_color_scheme_generator_print_form');
		add_action( 'admin_color_scheme_generator_print_form', array($this, 'print_form') );
	}

	/**
	 * Add the menu to the tools menu in admin
	 */
	function add_menu() {
		add_management_page(
			__( 'Admin Color Scheme Generator', 'admin-color-scheme-generator' ),
			__( 'Admin Color Scheme Generator', 'admin-color-scheme-generator' ),
			'manage_options',
			'admin-color-scheme-generator',
			array($this, 'render_tools_form')
		);
	}

	/**
	 * Render the form in the Tools page
	 */
	function render_tools_form() {
		?>
		<div class="wrap">
		<h2><?php _e( 'Admin Color Scheme Generator', 'admin-color-scheme-generator' ); ?></h2>
		<div class="metabox-holder">
			<p><?php _e( 'To create your very own admin color scheme, fill in the form and click "Generate". A stand-alone WordPress plugin zip file will be generated. Install and activate the generated plugin to add the new colour scheme.', 'admin-color-scheme-generator' ); ?></p>
			<div class="postbox">
				<?php $this->print_form(); ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Renders the generator form
	 */
	function print_form() {
		$css_path = plugin_dir_url( __FILE__ ) . 'css/';
		$js_path  = plugin_dir_url( __FILE__ ) . 'js/';

		wp_enqueue_style( 'minicolors', $css_path . 'jquery.minicolors.css', array(), ACSG_PLUGIN_VER );
		wp_enqueue_style( 'admin-color-scheme-generator', $css_path . 'admin-color-scheme-generator.css', array('minicolors'), ACSG_PLUGIN_VER );

		wp_enqueue_script( 'minicolors', $js_path . 'jquery.minicolors.min.js', array('jquery'), ACSG_PLUGIN_VER );
		wp_enqueue_script( 'admin-color-scheme-generator', $js_path . 'admin-color-scheme-generator.js', array('jquery', 'minicolors'), ACSG_PLUGIN_VER );

		?>
		<form id="acsg_form" method="POST">
			<input type="hidden" name="acsg_action" value="generate"/>
			<?php wp_nonce_field( 'acsg_nonce', 'acsg_nonce' ); ?>

			<section>
				<p>
					<label for="scheme_name"><?php _e( 'Color Scheme Name', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" id="scheme_name" name="scheme_name"
						   placeholder="<?php _e( 'Color Scheme Name', 'admin-color-scheme-generator' ); ?>"/>
				</p>

				<p>
					<label for="scheme_author"><?php _e( 'Scheme Author', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" id="scheme_author" name="scheme_author" value="<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ''; ?>"
						   placeholder="<?php _e( 'Scheme Author', 'admin-color-scheme-generator' ); ?>"/>
				</p>

				<p>
					<label for="scheme_base_color"><?php _e( 'Base Color', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" class="acsg-colorpicker" id="scheme_base_color" name="scheme_base_color"
						   placeholder="<?php _e( 'Base Color', 'admin-color-scheme-generator' ); ?>"/>
				</p>

				<p>
					<label
						for="scheme_highlight_color"><?php _e( 'Highlight Color', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" class="acsg-colorpicker" id="scheme_highlight_color"
						   name="scheme_highlight_color"
						   placeholder="<?php _e( 'Highlight Color', 'admin-color-scheme-generator' ); ?>"/>
				</p>

				<p>
					<label
						for="scheme_notification_color"><?php _e( 'Notification Color', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" class="acsg-colorpicker" id="scheme_notification_color"
						   name="scheme_notification_color"
						   placeholder="<?php _e( 'Notification Color', 'admin-color-scheme-generator' ); ?>"/>
				</p>

				<p>
					<label
						for="scheme_action_color"><?php _e( 'Action Color', 'admin-color-scheme-generator' ); ?></label>
					<input type="text" class="acsg-colorpicker" id="scheme_action_color" name="scheme_action_color"
						   placeholder="<?php _e( 'Action Color', 'admin-color-scheme-generator' ); ?>"/>
				</p>

			</section>

			<div>
				<input type="submit" name="acsg_submit"
					   value="<?php _e( 'Generate', 'admin-color-scheme-generator' ); ?>"/>
			</div>
		</form>
	<?php
	}

	/**
	 * Generates a zip file with the admin color scheme files
	 */
	function generate_color_scheme() {

		if ( empty($_POST['acsg_action']) || 'generate' != $_POST['acsg_action'] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST['acsg_nonce'], 'acsg_nonce' ) ) {
			return;
		}

		//include our dependencies
		require_once plugin_dir_path( __FILE__ ) . 'includes/phpscss/scss.inc.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/zip/class-wp_zip_generator.php';

		$this->scheme_name = $_REQUEST['scheme_name'];
		$this->scheme_slug = sanitize_title_with_dashes( $this->scheme_name );

		$variables = array(
			'{name}'               => sanitize_text_field( $this->scheme_name ),
			'{slug}'               => $this->scheme_slug,
			'{slug-class}'         => str_replace('-', '_', $this->scheme_slug),
			'{author}'             => sanitize_text_field( $_REQUEST['scheme_author'] ),
			'{base-color}'         => sanitize_text_field( $_REQUEST['scheme_base_color'] ),
			'{highlight-color}'    => sanitize_text_field( $_REQUEST['scheme_highlight_color'] ),
			'{notification-color}' => sanitize_text_field( $_REQUEST['scheme_notification_color'] ),
			'{action-color}'       => sanitize_text_field( $_REQUEST['scheme_action_color'] ),
			'{tool}'               => '<a href="http://themergency.com/generators/admin-color-scheme-generator" target="_blank">' . __( 'Admin Color Scheme Generator', 'admin-color-scheme-generator' ) . '</a>'
		);

		$zip_generator = new WP_Zip_Generator(array(
			'name'                 => 'admin-color-scheme-generator',
			'process_extensions'   => array('txt'),
			'source_directory'     => dirname( __FILE__ ) . '/plugin_template/',
			'zip_root_directory'   => "{$this->scheme_slug}-admin-color-scheme",
			'download_filename'    => "{$this->scheme_slug}-admin-color-scheme.zip",
			'filename_filter'      => array($this, 'process_zip_filename'),
			'file_contents_filter' => array($this, 'process_zip_file_contents'),
			'post_process_action'  => array($this, 'process_zip'),
			'variables'            => $variables
		));

		//generate the zip file
		$zip_generator->generate();

		//download it to the client
		$zip_generator->send_download_headers();

		die();
	}

	function process_zip_filename($filename) {
		if ( 'admin-color-scheme-plugin.php.txt' === $filename ) {

			return "{$this->scheme_slug}-admin-color-scheme.php";

		} else if ( 'colors.scss.txt' === $filename ) {

			return "{$this->scheme_slug}.scss";

		}

		return $filename;
	}

	function process_zip_file_contents($contents, $filename) {
		if ( 'colors.scss.txt' === $filename ) {

			//store the scss for later use
			$this->scss_contents = $contents;
		}

		return $contents;
	}

	function process_zip($zip, $options) {
		//we need to generate our css file and add it to the zip

		$scss = new scssc();

		$scss->setImportPaths( plugin_dir_path( __FILE__ ) . 'plugin_template' );

		$css = $scss->compile( $this->scss_contents );

		$zip->addFromString( trailingslashit( $options['zip_root_directory'] ) . $this->scheme_slug . '.css', $css );
	}
}

global $admin_color_scheme_gen;
$admin_color_scheme_gen = new Admin_Color_Scheme_Generator();