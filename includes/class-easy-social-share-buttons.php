<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Social_Share_Buttons {

	/**
	 * The single instance of Easy_Social_Share_Buttons.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'easy_social_share_buttons';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Easy_Social_Share_Buttons_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		//Add post content filters
		add_filter('the_content', array( $this, 'add_share_buttons_to_post'));
		add_filter('the_content', array( $this, 'add_share_buttons_to_media'));

		//Create shortcode
		add_shortcode( 'ess_post', array($this, 'share_post_shortcode') );

	} // End __construct ()

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		if (get_option('ess_load_css')) {
			wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-frontend' );
		}
	} // End enqueue_styles ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'easy-social-share-buttons', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'easy-social-share-buttons';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Easy_Social_Share_Buttons Instance
	 *
	 * Ensures only one instance of Easy_Social_Share_Buttons is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Easy_Social_Share_Buttons()
	 * @return Main Easy_Social_Share_Buttons instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Return the url of the current page.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_current_url() {
		global $wp;
		return home_url(add_query_arg(array(),$wp->request)) . '/';
	} // End get_current_url()

	/**
	 * Return the number of shares on Facebook.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_facebook_count($url) {
		//https://api.facebook.com/method/links.getStats?urls=%%URL%%&format=json

		$endpoint = 'https://api.facebook.com/method/links.getStats?urls=' . $url . '&format=json';

		// setup curl to make a call to the endpoint
		$session = curl_init($endpoint);

		// indicates that we want the response back rather than just returning a "TRUE" string
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// execute GET and get the session back
		$result = json_decode(curl_exec($session));

		// close connection
		curl_close($session);

		return $result[0]->total_count;
	} // End get_facebook_count()

	/**
	 * Return the number of shares on Twitter.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_twitter_count($url) {
		//http://urls.api.twitter.com/1/urls/count.json?url=%%URL%%&callback=twttr.receiveCount  

		$endpoint = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		# Get the response
		$response = curl_exec($curl);

		# Close connection
		curl_close($curl);

		# Return JSON
		$result = json_decode($response, true);

		return $result["count"];
	} // End get_twitter_count()

	/**
	 * Return the number of shares on Pinterest.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_pinterest_count($url) {
		//https://api.pinterest.com/v1/urls/count.json?callback=jsonp&url=%%URL%%

		$endpoint = 'https://api.pinterest.com/v1/urls/count.json?callback=jsonp&url=' . $url;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		# Get the response
		$response = curl_exec($curl);

		# Close connection
		curl_close($curl);

		# Return JSON
		$response = str_replace(array('jsonp(', ')'), '', $response);
		$result = json_decode($response, true);

		return $result['count'];
	} // End get_pinterest_count()

	/**
	 * Return the number of shares on Google Plus.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_google_count($url) {

		$html =  file_get_contents( "https://plusone.google.com/_/+1/fastbutton?url=" . $url );
		$doc = new DOMDocument();   
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);
		$counter=$doc->getElementById('aggregateCount');
		libxml_clear_errors();
		return $counter->nodeValue;
	} // End get_google_count()

	/**
	 * Create and return a post excerpt from a post ID outside of the loop.
	 * A similar function in Wordpress was deprecated.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_excerpt_by_id($post_id){
	    $the_post = get_post($post_id); //Gets post ID
	    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	    $excerpt_length = 35; //Sets excerpt length by word count
	    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

	    if(count($words) > $excerpt_length) :
	        array_pop($words);
	        array_push($words, '…');
	        $the_excerpt = implode(' ', $words);
	    endif;

	    return $the_excerpt;
	} //End get_excerpt_by_id()

	/**
	 * Encode the url query parameters in hex to preserve special characters
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function mb_rawurlencode($url){
		$encoded='';
		$length=mb_strlen($url);

		for ($i=0;$i<$length;$i++) {
			$encoded.='%'.wordwrap(bin2hex(mb_substr($url,$i,1)),2,'%',true);
		}

		return $encoded;
	} // End mb_rawurlencode()

	/**
	 * Generate buttons
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_button_html($social_site, $post_id, $show_count, $image_url = null, $image_id = null) {

		$title = $this->mb_rawurlencode( get_the_title( $post_id ) );
		$description = $this->mb_rawurlencode( $this->get_excerpt_by_id( $post_id ) );
		$link = rawurlencode( get_permalink( $post_id ) );
		$direct_link = get_permalink( $post_id ) . ($image_id ? '/#' . $image_id : '');
		$current_url = $this->get_current_url();
		$button = '';

		switch ($social_site) {
			case 'facebook':

			    $facebook_share_url = 'https://www.facebook.com/dialog/feed?';
			    $facebook_share_url .= 'app_id=' . esc_html( get_option('ess_facebook_app_id') );
			    $facebook_share_url .= '&amp;display=popup';
			    $facebook_share_url .= '&amp;caption=' . $title;
			    $facebook_share_url .= '&amp;link=' . $link;
			    $facebook_share_url .= '&amp;description=' . $description;
			    $facebook_share_url .= '&amp;redirect_uri=' . $link;

			    if ($image_url) {
			    	$facebook_share_url .= '&amp;picture=' . rawurlencode( $image_url );
			    }

				$button = '<a class="ess-button ess-button--facebook" href="' . $facebook_share_url . '" onclick="window.open(this.href, \'facebookwindow\',\'left=20,top=20,width=600,height=700,toolbar=0,resizable=1\'); return false;" title="Share on Facebook" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><path d="M853.35 0h-682.702c-94.25 0-170.648 76.42-170.648 170.686v682.63c0 94.266 76.398 170.684 170.648 170.684h341.352v-448h-128v-128h128v-96c0-88.366 71.634-160 160-160h160v128h-160c-17.674 0-32 14.328-32 32v96h176l-32 128h-144v448h213.35c94.25 0 170.65-76.418 170.65-170.684v-682.63c0-94.266-76.4-170.686-170.65-170.686z"></path></svg>' . "\n";
				$button .= '<span class="ess-share-text">Share</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_facebook_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'twitter':
				$twitter_url = 'http://twitter.com/intent/tweet?';
				$twitter_url .= 'text=' . $title . '%20' . $link;

				$button = '<a class="ess-button ess-button--twitter" href="' . $twitter_url .'" onclick="window.open(this.href, \'twitterwindow\',\'left=20,top=20,width=600,height=300,toolbar=0,resizable=1\'); return false;" title="Tweet" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><path d="M1024 194.418c-37.676 16.708-78.164 28.002-120.66 33.080 43.372-26 76.686-67.17 92.372-116.23-40.596 24.078-85.556 41.56-133.41 50.98-38.32-40.83-92.922-66.34-153.346-66.34-116.022 0-210.088 94.058-210.088 210.078 0 16.466 1.858 32.5 5.44 47.878-174.6-8.764-329.402-92.4-433.018-219.506-18.084 31.028-28.446 67.116-28.446 105.618 0 72.888 37.088 137.192 93.46 174.866-34.438-1.092-66.832-10.542-95.154-26.278-0.020 0.876-0.020 1.756-0.020 2.642 0 101.788 72.418 186.696 168.522 206-17.626 4.8-36.188 7.372-55.348 7.372-13.538 0-26.698-1.32-39.528-3.772 26.736 83.46 104.32 144.206 196.252 145.896-71.9 56.35-162.486 89.934-260.916 89.934-16.958 0-33.68-0.994-50.116-2.94 92.972 59.61 203.402 94.394 322.042 94.394 386.422 0 597.736-320.124 597.736-597.744 0-9.108-0.206-18.168-0.61-27.18 41.056-29.62 76.672-66.62 104.836-108.748z"></path></svg>' . "\n";
				$button .= '<span class="ess-share-text">Tweet</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_twitter_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;
			
			case 'gplus':
				$google_url = 'https://plus.google.com/share?';
				$google_url .= 'url=' . $link;

				$button = '<a class="ess-button ess-button--gplus" href="' . $google_url .'" onclick="javascript:window.open(this.href,\'googlepluswindow\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" title="Share on Google" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><path d="M559.066 64c0 0-200.956 0-267.94 0-120.12 0-233.17 91.006-233.17 196.422 0 107.726 81.882 194.666 204.088 194.666 8.498 0 16.756-0.17 24.842-0.752-7.93 15.186-13.602 32.288-13.602 50.042 0 29.938 16.104 54.21 36.468 74.024-15.386 0-30.242 0.448-46.452 0.448-148.782-0.002-263.3 94.758-263.3 193.020 0 96.778 125.542 157.314 274.334 157.314 169.624 0 263.306-96.244 263.306-193.028 0-77.6-22.896-124.072-93.686-174.134-24.216-17.144-70.53-58.836-70.53-83.344 0-28.72 8.196-42.868 51.428-76.646 44.312-34.624 75.672-83.302 75.672-139.916 0-67.406-30.020-133.098-86.372-154.772h84.954l59.96-43.344zM465.48 719.458c2.126 8.972 3.284 18.206 3.284 27.628 0 78.2-50.392 139.31-194.974 139.31-102.842 0-177.116-65.104-177.116-143.3 0-76.642 92.126-140.444 194.964-139.332 24 0.254 46.368 4.116 66.67 10.69 55.826 38.826 95.876 60.762 107.172 105.004zM300.818 427.776c-69.038-2.064-134.636-77.226-146.552-167.86-11.916-90.666 34.37-160.042 103.388-157.99 69.010 2.074 134.638 74.814 146.558 165.458 11.906 90.66-34.39 162.458-103.394 160.392zM832 256v-192h-64v192h-192v64h192v192h64v-192h192v-64z"></path></svg>' . "\n";
				$button .= '<span class="ess-share-text">Share</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_google_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'pinterest':
				$pinterest_url = 'http://pinterest.com/pin/create/bookmarklet/';
				$pinterest_url .= '?media=' . rawurlencode( $image_url );
				$pinterest_url .= '&amp;url=' . $link;
				$pinterest_url .= '&amp;is_video=false';
				$pinterest_url .= '&amp;description=' . $title . rawurlencode(' – ') . $description;

				$button = '<a class="ess-button ess-button--pinterest" href="' . $pinterest_url . '" onclick="window.open(this.href, \'pinterestwindow\',\'left=20,top=20,width=750,height=750,toolbar=0,resizable=1\');" title="Pin" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><path d="M512.006 0.002c-282.774 0-512.006 229.23-512.006 511.996 0 216.906 134.952 402.166 325.414 476.772-4.478-40.508-8.518-102.644 1.774-146.876 9.298-39.954 60.040-254.5 60.040-254.5s-15.32-30.664-15.32-76.008c0-71.19 41.268-124.336 92.644-124.336 43.68 0 64.784 32.794 64.784 72.12 0 43.928-27.964 109.604-42.404 170.464-12.060 50.972 25.556 92.536 75.814 92.536 91.008 0 160.958-95.96 160.958-234.466 0-122.584-88.088-208.298-213.868-208.298-145.678 0-231.186 109.274-231.186 222.19 0 44.008 16.95 91.196 38.102 116.844 4.182 5.070 4.792 9.516 3.548 14.68-3.884 16.18-12.522 50.96-14.216 58.076-2.234 9.368-7.422 11.356-17.124 6.842-63.95-29.77-103.926-123.264-103.926-198.348 0-161.51 117.348-309.834 338.294-309.834 177.61 0 315.634 126.56 315.634 295.704 0 176.458-111.256 318.466-265.676 318.466-51.886 0-100.652-26.958-117.35-58.796 0 0-25.672 97.766-31.894 121.71-11.564 44.468-42.768 100.218-63.642 134.226 47.91 14.832 98.818 22.832 151.604 22.832 282.768-0.002 511.996-229.23 511.996-512 0-282.766-229.228-511.996-511.994-511.996z"></path></svg>' . "\n";
				$button .= '<span class="ess-share-text">Pin</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_pinterest_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'email':

				$email_url = 'mailto:';
				$email_url .= '?subject=' . $title;
				$email_url .= '&amp;body=' . $description . '%20%20' . rawurlencode($direct_link);

				$button = '<a class="ess-button ess-button--email" href="' . $email_url . '" title="Email">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><path d="M928 128h-832c-52.8 0-96 43.2-96 96v640c0 52.8 43.2 96 96 96h832c52.8 0 96-43.2 96-96v-640c0-52.8-43.2-96-96-96zM398.74 550.372l-270.74 210.892v-501.642l270.74 290.75zM176.38 256h671.24l-335.62 252-335.62-252zM409.288 561.698l102.712 110.302 102.71-110.302 210.554 270.302h-626.528l210.552-270.302zM625.26 550.372l270.74-290.75v501.642l-270.74-210.892z"></path></svg>' . "\n";
				$button .= '<span class="ess-share-text">Email</span>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '</a>' . "\n";
			break;

			case 'link':

				$button = '<a class="ess-button ess-button--link" href="' . $direct_link . '" title="Share Direct Link" onclick="event.preventDefault(); ">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><g><path class="path1" d="M440.236 635.766c-13.31 0-26.616-5.076-36.77-15.23-95.134-95.136-95.134-249.934 0-345.070l192-192c46.088-46.086 107.36-71.466 172.534-71.466s126.448 25.38 172.536 71.464c95.132 95.136 95.132 249.934 0 345.070l-87.766 87.766c-20.308 20.308-53.23 20.308-73.54 0-20.306-20.306-20.306-53.232 0-73.54l87.766-87.766c54.584-54.586 54.584-143.404 0-197.99-26.442-26.442-61.6-41.004-98.996-41.004s-72.552 14.562-98.996 41.006l-192 191.998c-54.586 54.586-54.586 143.406 0 197.992 20.308 20.306 20.306 53.232 0 73.54-10.15 10.152-23.462 15.23-36.768 15.23z"></path><path class="path2" d="M256 1012c-65.176 0-126.45-25.38-172.534-71.464-95.134-95.136-95.134-249.934 0-345.070l87.764-87.764c20.308-20.306 53.234-20.306 73.54 0 20.308 20.306 20.308 53.232 0 73.54l-87.764 87.764c-54.586 54.586-54.586 143.406 0 197.992 26.44 26.44 61.598 41.002 98.994 41.002s72.552-14.562 98.998-41.006l192-191.998c54.584-54.586 54.584-143.406 0-197.992-20.308-20.308-20.306-53.232 0-73.54 20.306-20.306 53.232-20.306 73.54 0.002 95.132 95.134 95.132 249.932 0.002 345.068l-192.002 192c-46.090 46.088-107.364 71.466-172.538 71.466z"></path></g></svg>' . "\n";
				$button .= '<span class="ess-share-text">' . $direct_link . '</span>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '<div class="ess-share-link-wrap">' . "\n";
				$button .= '<input class="ess-share-link" type="text" value="' . $direct_link . '" onclick="this.setSelectionRange(0, this.value.length)"/>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '</a>' . "\n";
			break;

			default:
			break;
		}

		return $button;
	}// End get_button_html()

	/**
	 * Build the html the share button component
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function build_share_buttons($show_count) {
		global $post;

		$html = '<ul class="ess-buttons' . ($show_count ? ' ess-buttons--count' : '') . '">' . "\n";

		$sites = get_option('ess_social_sites');

		foreach($sites as $site) {

			$button = $this->get_button_html($site, $post->ID, $show_count, get_the_post_thumbnail( $post->ID ));

			$html .= '<li>' . $button . '</li>' . "\n";
		}

		$html .= '</ul>' . "\n";

		return $html;
	} // End build_share_buttons()

	/**
	 * Add the social sharing buttons either before or after the post content.
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function add_share_buttons_to_post($content) {
		$location = get_option('ess_share_location');

		$button_type = get_option( 'ess_share_type' );
		$show_count = false;

		if ($button_type == 'count') {
			$show_count = true;
		}

		foreach($location as $position) {
			if ($position == 'before') {
				$content = $this->build_share_buttons($show_count) . $content;
			}
			if ($position == 'after') {
				$content = $content . $this->build_share_buttons($show_count);
			}
		}
		

		return $content;
	} // End add_share_buttons_to_post()

	

	/**
	 * Shortcode for adding the sharing buttons to content or templates
	 * [ess_post], [ess_post share_type="count"], <?php echo do_shortcode('[ess_post share_type="count"]'); ?>
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function share_post_shortcode( $atts, $content = null ) {
		$options = shortcode_atts( array(
			'share_type' => 'basic'
		), $atts );

		$show_count = false;

		if ($options['share_type'] == 'count') {
			$show_count = true;
		}

		return $this->build_share_buttons($show_count);
	} // End share_post_shortcode()

	/**
	 * Add the social sharing buttons to post media.
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function add_share_buttons_to_media($content) {
		global $post;

		if (get_option('ess_load_css')) {
	
			$content = do_shortcode($content);

			$wrap_class = 'ess-image-wrap';	
		  
			if ( trim( $content ) != '' ) {

				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
			
				$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
				
				$wrapper_div = $dom->createElement('div');
				$wrapper_div->setAttribute('class', $wrap_class);

				$ImageList = $dom->getElementsByTagName('img');

				foreach ( $ImageList as $key => $Image ) {

					$share_list = $dom->createElement('ul');
					$share_list->setAttribute('class', 'ess-buttons');

					$sites = get_option('ess_media_social_sites');

					$random_id = substr(base64_encode(basename($Image->getAttribute('src'))), 0, 15);

					foreach($sites as $site) {

						$button = $this->get_button_html($site, $post->ID, false, $Image->getAttribute('src'), $random_id);

						$share_item = $dom->createDocumentFragment();
						$share_item->appendXML($button);
						$share_list->appendChild( $share_item );

					}//End social sites foreach

					if ( $Image->parentNode->nodeName == 'a' ) {

						$link_parent = $Image->parentNode;

						$wrap_clone = $wrapper_div->cloneNode();

						$wrap_clone->setAttribute('id', $random_id);

						$link_parent->parentNode->replaceChild($wrap_clone, $link_parent);
						$wrap_clone->appendChild($link_parent);

						$wrap_clone->appendChild( $share_list );
						
					} else {
						
						$wrap_clone = $wrapper_div->cloneNode();

						$wrap_clone->setAttribute('id', $random_id);

						$Image->parentNode->replaceChild($wrap_clone, $Image);
						$wrap_clone->appendChild($Image);

						$wrap_clone->appendChild( $share_list );
						
					}

				}//End Images foreach
			

				//Fixed the issue with additional html tags loading
				$content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));

			}// End if has content

		}//End if media share option on

	   // return the processed content
	   return $content;
	
	} //End add_share_buttons_to_media()

}//End class Easy_Social_Share_Buttons
