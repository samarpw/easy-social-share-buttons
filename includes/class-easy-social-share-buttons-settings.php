<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Social_Share_Buttons_Settings {

	/**
	 * The single instance of Easy_Social_Share_Buttons_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'ess_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Easy Social Share Button Settings', 'easy-social-share-buttons' ) , __( 'Easy Social Share', 'easy-social-share-buttons' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'easy-social-share-buttons' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['post_share'] = array(
			'title'					=> __( 'Post Share Buttons', 'easy-social-share-buttons' ),
			'description'			=> __( 'Options for showing social sharing buttons on posts', 'easy-social-share-buttons' ),
			'fields'				=> array(
				array(
					'id' 			=> 'share_type',
					'label'			=> __( 'Share Button Type', 'easy-social-share-buttons' ),
					'description'	=> __( 'Show basic share buttons, or share buttons that also show the number of shares.', 'easy-social-share-buttons' ),
					'type'			=> 'radio',
					'options'		=> array( 'basic' => 'Basic (icon only)', 'text' => 'Icon and Text', 'count' => 'Share Count'),
					'default'		=> 'basic'
				),
				array(
					'id' 			=> 'share_location',
					'label'			=> __( 'Share Button Location', 'easy-social-share-buttons' ),
					'description'	=> __( 'Select where you would like the sharing buttons to display.', 'easy-social-share-buttons' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'before' => 'Before Post', 'after' => 'After Post'),
					'default'		=> array( 'before' )
				),
				array(
					'id' 			=> 'social_sites',
					'label'			=> __( 'Social Sites', 'easy-social-share-buttons' ),
					'description'	=> __( 'Select which sharing buttons you would like to display.', 'easy-social-share-buttons' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'gplus' => 'Google Plus', 'pinterest' => 'Pinterest', 'email' => 'Email'),
					'default'		=> array( 'facebook', 'twitter' )
				),
				array(
					'id' 			=> 'facebook_app_id',
					'label'			=> __( 'Facebook App Id' , 'easy-social-share-buttons' ),
					'description'	=> __( 'Facebook Sharing requires that you register an app with Facebook. See below for instructions.', 'easy-social-share-buttons' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'ex: XXXXXXXXXXXXXXX', 'easy-social-share-buttons' )
				)
			)
		);

		$settings['media_share'] = array(
			'title'					=> __( 'Media Share Buttons', 'easy-social-share-buttons' ),
			'description'			=> __( 'Options for displaying share buttons on post media.', 'easy-social-share-buttons' ),
			'fields'				=> array(
				array(
					'id' 			=> 'show_media_buttons',
					'label'			=> __( 'Display Media Share Buttons', 'easy-social-share-buttons' ),
					'description'	=> __( '', 'easy-social-share-buttons' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'media_social_sites',
					'label'			=> __( 'Social Sites', 'easy-social-share-buttons' ),
					'description'	=> __( 'Select which sharing buttons you would like to display.', 'easy-social-share-buttons' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'facebook' => 'Facebook', 'pinterest' => 'Pinterest', 'email' => 'Email', 'link' => 'Direct Link'),
					'default'		=> array( 'facebook', 'pinterest' )
				)
			)
		);

		$settings['advanced'] = array(
			'title'					=> __( 'Advanced Options', 'easy-social-share-buttons' ),
			'description'			=> __( 'More customization for experienced Wordpress Theme developers.', 'easy-social-share-buttons' ),
			'fields'				=> array(
				array(
					'id' 			=> 'load_css',
					'label'			=> __( 'Enqueue plugin CSS', 'easy-social-share-buttons' ),
					'description'	=> __( 'Turn this off if you want to include this plugin\'s CSS within your theme\'s CSS to reduce HTTP requests and increase page load speed, or if you want to customize the buttons with your own styles.', 'easy-social-share-buttons' ),
					'type'			=> 'checkbox',
					'default'		=> 'on'
				)
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Easy Social Share Settings' , 'easy-social-share-buttons' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'easy-social-share-buttons' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";

			$html .= '<h3>' . __('Shortcodes', 'easy-social-share-buttons') . '</h3>' . "\n";
			$html .= '<p>' . __('Use this shortcode to add sharing buttons to your content or in your template files.', 'easy-social-share-buttons') . '</p>' . "\n";
			$html .= '<pre>[ess_post]</pre><pre>[ess_post share_type="count"]</pre><pre>[ess_post share_type="text"]</pre>' . "\n";
			$html .= '<pre>&lt;?php echo do_shortcode( \'[ess_post]\' ); ?&gt;</pre>' . "\n";
			$html .= '<h3>' . __('Facebook App Id', 'easy-social-share-buttons') . '</h3>' . "\n";
			$html .= '<p>' . __('Sharing on facebook requires that you have a Facebook App Id.', 'easy-social-share-buttons') . '</p>' . "\n";
			$html .= '<ol>' . "\n";
			$html .= '<li>' . __('Go to ', 'easy-social-share-buttons') . '<a href="https://developers.facebook.com/">https://developers.facebook.com/</a>.</li>' . "\n";
			$html .= '<li>' . __('Log in using your existing Facebook account or create a new one.', 'easy-social-share-buttons') . '</li>' . "\n";
			$html .= '<li>' . __('Under "My Apps" in the header, select "Add a New App".', 'easy-social-share-buttons') . '</li>' . "\n";
			$html .= '<li>' . __('Select "website".', 'easy-social-share-buttons') . '</li>' . "\n";
			$html .= '<li>' . __( 'Choose a name for your new app.', 'easy-social-share-buttons' ) . '</li>' . "\n";
			$html .= '<li>' . __('Skip the quick set-up and go to your app\'s dashboard. The App ID will be at the top under your app\'s name.', 'easy-social-share-buttons') . '</li>' . "\n";
			$html .= '<li>' . __('Under "Settings", fill in the fields for "Namespace" and "Contact Email".', 'easy-social-share-buttons') . '</li>' . "\n";
			$html .= '<li>' . __('Finally, go to "Status & Review" and click the button to make your app public.', 'easy-social-share-buttons') . '</li>' . "\n";
			

		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main Easy_Social_Share_Buttons_Settings Instance
	 *
	 * Ensures only one instance of Easy_Social_Share_Buttons_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Easy_Social_Share_Buttons()
	 * @return Main Easy_Social_Share_Buttons_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
