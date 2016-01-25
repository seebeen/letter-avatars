<?php 

/**
 * @package SGI\LTRAV
 */



/**
 * Main bakend class for all WP-Admin hooks and gimmicks
 * 
 * This class loads all of our backend hooks and sets up admin interfaces
 * 
 * @subpackage Admin Interfaces
 * @author Sibin Grasic
 * @since 1.0
 * @var version - plugin version
 * @var opts - plugin opts
 */
Class SGI_LtrAv_Backend
{

	private $version;
	private $opts;


	/**
	 * Letter Avatars Admin constructor
	 * 
	 * Constructro first checks if plugin version exists in DB. If this is the first activation, plugin adds version info to the DB with autoload option set to false
	 * In that manner we can easily change plugin options, and add further defaults across versions, and preserve compatibility
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function __construct()
	{
		
		if ($ltrav_ver = get_option('sgi_ltrav_ver')) :

			if (version_compare(SGI_LTRAV_VERSION,$ltrav_ver,'>')) :
				update_option('sgi_fitvids_ver', SGI_LTRAV_VERSION);
			endif;

			$this->version = SGI_LTRAV_VERSION;

		else :

			$ltrav_ver = SGI_LTRAV_VERSION;
			add_option('sgi_ltrav_ver',$ltrav_ver,'no');

		endif;

		$ltrav_opts = get_option(
			'sgi_ltrav_opts',
			array(
				'use_gravatar' => true,
				'style' 	   => array(
					'rand_color'   => false,
					'color'		   => '#FFF',
					'bg_color'	   => '#000',
					'padding'	   => '10 10',
				),
				'font'		   => array(
					'load_gfont'   => true,
					'font_name'	   => 'Roboto',
					'gfont_style'  => '',
					'font_size'	   => '14',
					//'font_style'   => '',
				)
		));

		$this->opts = $ltrav_opts;

		add_filter('plugin_action_links_'.SGI_LTRAV_BASENAME, array(&$this,'add_settings_link'));
		add_action('admin_init', array(&$this,'register_settings'));
		add_action('admin_enqueue_scripts',array(&$this,'add_admin_scripts'));

	}

	/**
	 * Function that adds admin scripts and styles to wp-admin
	 * We check if we're on the discussion options page, and if we're not, we bail out immediately.
	 * 
	 * If we are, we're loading Chosen css and js files, along with colorpicker, and our own admin js to handle some nifty thing
	 * @param string $hook - admin page that's being loaded
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 * 
	 */
	public function add_admin_scripts($hook)
	{
		if ($hook !== 'options-discussion.php')
			return;

		wp_register_style( 'ltrav-admin-chosen', plugins_url('assets/css/chosen.min.css',SGI_LTRAV_BASENAME), null, SGI_LTRAV_VERSION );

		wp_enqueue_style('ltrav-admin-chosen');
		wp_enqueue_style('wp-color-picker');

		wp_register_script( 'ltrav-admin-chosen-js', plugins_url( "assets/js/chosen.jquery.min.js", SGI_LTRAV_BASENAME ), array('jquery'), SGI_LTRAV_VERSION, true);
		wp_register_script( 'ltrav-admin-js', plugins_url( "assets/js/ltrav-admin.js", SGI_LTRAV_BASENAME ), array('jquery', 'wp-color-picker'), SGI_LTRAV_VERSION, true);

		wp_enqueue_script('ltrav-admin-chosen-js');
		wp_enqueue_script('ltrav-admin-js');
	}

	/**
	 * Function that adds settings link to the plugin page
	 * @param array $links 
	 * @return array - merged array with our links
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function add_settings_link($links)
	{
		$admin_url = admin_url();
		$link = array("<a href=\"${admin_url}options-discussion.php#sgi-ltrav\">Settings</a>");

		return array_merge($links,$link);
	}

	/**
	 * Function that is hooked into the admin initialisation and registers settings
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function register_settings()
	{
		add_settings_section('sgi_ltrav_opts','Letter Avatars',array(&$this, 'settings_section_info'), 'discussion');
		add_settings_field('sgi_ltrav_opts[use_gravatar]', __('Gravatar Options','sgifitvids'), array(&$this,'gravatar_callback'), 'discussion', 'sgi_ltrav_opts', $this->opts['use_gravatar']);
		add_settings_field('sgi_ltrav_opts[style]', __('Styling options','sgifitvids'), array(&$this,'style_callback'), 'discussion', 'sgi_ltrav_opts', $this->opts['style']);
		add_settings_field('sgi_ltrav_opts[font]', __('Font options','sgifitvids'), array(&$this,'font_callback'), 'discussion', 'sgi_ltrav_opts', $this->opts['font']);

		register_setting('discussion','sgi_ltrav_opts',array(&$this,'sanitize_opts'));
	}

	/**
	 * Function that generates gravatar override checkbox
	 * @param boolean $use_gravatar - Gravatar options
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function gravatar_callback($use_gravatar)
	{
		$use_gravatar = checked($use_gravatar,true,false);

		echo "<input type=\"checkbox\" name=\"sgi_ltrav_opts[use_gravatar]\" {$use_gravatar} ><label>Show gravatar if available</label><br>";
		echo "<small>If you check this options, Gravatar™ will be shown for those users that have it.</small>";
	}

	/**
	 * Function that generates style options for the letter avatar
	 * @param array $style - Style options
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function style_callback($style)
	{

		$rand_color = checked($style['rand_color'],true,false);

		// Random color
		echo "<input type=\"checkbox\" name=\"sgi_ltrav_opts[style][rand_color]\" {$rand_color} ><label>Randomize color</label><br>";
		echo "<small>If you check this option, random colors will be used for every comment</small><br><br>";

		// Background color
		echo "<label><strong>Background color</strong></label><br>";
		echo "<input id=\"ltrav-bg-color\" type=\"text\" name=\"sgi_ltrav_opts[style][bg_color]\" value=\"{$style['bg_color']}\">".'<br>';
		echo "<small>Background color for Letter Avatars</small><br><br>";

		// Font color
		echo "<label><strong>Font color</strong></label><br>";
		echo "<input id=\"ltrav-font-color\" type=\"text\" name=\"sgi_ltrav_opts[style][color]\" value=\"{$style['color']}\">".'<br>';
		echo "<small>Font color for Letter Avatars</small><br><br>";

		// Padding
		echo "<label><strong>Letter padding</strong></label><br>";
		echo "<input type=\"text\" name=\"sgi_ltrav_opts[style][padding]\" value=\"{$style['padding']}\">".'<br>';
		echo "<small>Padding for the letters - Allows CSS syntax (without px)</small><br><br>";
	}
	
	/**
	 * Function that generates font options for the letter avatar
	 * @param array $font - font options
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function font_callback($font)
	{
		$load_gfont = checked($font['load_gfont'],true,false);
		$style = ($font['load_gfont'] == true) ? "display:block;" : "display:block	;";

		// Load Google Font
		
		echo "<label><strong>Google Fonts</strong></label><br>";
		echo "<input type=\"checkbox\" name=\"sgi_ltrav_opts[font][load_gfont]\" {$load_gfont} ><label>Load Google Fonts CSS</label><br>";
		echo "<small>If you check this option, you can select google font for the Letter avatar below.</small><br><br>";

		// Google Font selector
		echo "<div style=\"${style}\">";
		$this->generate_gfont_select($font['font_name'],$font['gfont_style']);
		echo '</div>';

		// Font size
		echo "<label><strong>Font size</strong></label><br>";
		echo "<input type=\"text\" name=\"sgi_ltrav_opts[font][font_size]\" value=\"{$font['font_size']}\">".'<br>';
		echo "<small>Font size - in px</small><br><br>";

	}

	/**
	 * Function that displays the section heading information
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function settings_section_info()
	{
		echo '<div id="sgi-ltrav"></div><p>'.__('These are the settings for the Letter Avatars','sgiltrav').'</p>';
	}


	/**
	 * Function that generates select boxes for google fonts.
	 * We generate a select box for all the google fonts, with custom data-var variable that lists available styles for the font
	 * 
	 * @param string $selected_font - Selected font for the letter avatar
	 * @param string $selected_style - Selected font style for the letter avatar
	 * @return void
	 */
	private function generate_gfont_select($selected_font,$selected_style)
	{
		$font_list = $this->get_google_font_list();


		if (!$font_list) :
			echo '<strong>Something went wrong. Cannot get google font list';
			return;
		endif;

		$sel_font_array = null;

		echo '<select id="ltrav-gfont-select" name="sgi_ltrav_opts[font][font_name]">';
		foreach ($font_list['items'] as $font ):

			$font_name = $font['family'];
			$variants = implode(',',$font['variants']);
			$selected = selected( $selected_font, $font_name, false );

			if ($selected_font == $font_name) :
				$sel_font_array = $font;
			endif;

			echo "<option value=\"{$font_name}\" {$selected} data-var=\"$variants\">{$font_name}</option>";

		endforeach;
		echo '/<select>';


		echo '<select id="ltrav-gfont-style" name="sgi_ltrav_opts[font][gfont_style]>';
		foreach ($sel_font_array['variants'] as $variant) :

			$selected = selected($selected_style,$variant,false);

			echo "<option value=\"{$variant}\" ${selected}>${variant}</option>";

		endforeach;
		echo '</select><br>';
	}

	/**
	 * Function that gets the complete google fonts list
	 * @return array - JSON decoded font list from google server
	 * @author Sibin Grasic
	 * @since 1.0
	 * @todo Add transient caching to prevent google api abuse / overload
	 */
	private function get_google_font_list()
	{
		$url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyC2XWzS33ZIlkC17s5GEX31ltIjOffyP5o';

		$font_list = wp_remote_get($url);

		if (!is_wp_error( $font_list )) :
			$font_list = $font_list['body'];
		else :
			$font_list = false;
		endif;

		return json_decode($font_list,true);

	}

	/**
	 * Function that "sanitizes options"
	 * 
	 * @param array $opts
	 * @return array - Sanitized options array
	 * @author Sibin Grasic
	 * @since 1.0
	 * 
	 */
	public function sanitize_opts($opts)
	{

		if (isset($opts['use_gravatar'])):
			$opts['use_gravatar'] = true;
		else :
			$opts['use_gravatar'] = false;
		endif;

		if ( isset($opts['style']['rand_color']) ) :
			$opts['style']['rand_color'] = true;
		else :
			$opts['style']['rand_color'] = false;
		endif;

		if (isset($opts['font']['load_gfont'])) :
			$opts['font']['load_gfont'] = true;
		else :
			$opts['font']['load_gfont'] = false;
		endif;

		$opts['style']['padding'] = strtr($opts['style']['padding'],array('px' => ''));

		if ($opts['font']['gfont_style'] == '') :
			$opts['font']['gfont_style'] = 'regular';
		endif;

		return $opts;
	}

}