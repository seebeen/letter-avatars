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
 */
Class SGI_LtrAv_Backend
{
	/**
	 * @var version - plugin version
	 */
	private $version;

	/**
	 * @var opts - plugin opts
	 */
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
				update_option('sgi_ltrav_ver', SGI_LTRAV_VERSION);
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
					'lock_color'   => false,
					'color'		   => '#FFF',
					'bg_color'	   => '#000',
				),
				'font'		   => array(
					'load_gfont'   => true,
					'font_name'	   => 'Roboto',
					'gfont_style'  => '',
					'font_size'	   => '14',
				)
		));

		$this->opts = $ltrav_opts;

		add_action('admin_init', array(&$this,'register_settings'));
		add_action('admin_menu', array(&$this, 'add_settings_menu'));

		add_filter('plugin_action_links_'.SGI_LTRAV_BASENAME, array(&$this,'add_settings_link'), 20, 1);

		add_action('admin_enqueue_scripts',array(&$this,'add_admin_scripts'));

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

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			admin_url('options-general.php?page=sgi-letter-avatars'),
			__('Settings','letter-avatars')
		);

		return $links;

	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function add_settings_menu()
	{

		add_submenu_page(
            'options-general.php',
            __('Letter Avatars','letter-avatars'),
            __('Letter Avatars','letter-avatars'),
            'manage_options',
            'sgi-letter-avatars',
            array(&$this, 'settings_callback')
        );

	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function settings_callback()
	{

		printf (
			'<div class="wrap"><div id="sgi-ltrav"><h1>%s</h1>',
			__('Letter Avatars Settings','letter-avatars')
		);

        echo '<form method="POST" action="options.php">';

        settings_fields('sgi_ltrav_settings');

        do_settings_sections('sgi-letter-avatars');

        submit_button();

        echo "</form>";

        echo '</div></div>';

	}

	/**
	 * Function that is hooked into the admin initialisation and registers settings
	 * @return void
	 * @author Sibin Grasic
	 * @since 1.0
	 */
	public function register_settings()
	{

		register_setting(
			'sgi_ltrav_settings',
			'sgi_ltrav_opts',
			array(&$this,'sanitize_opts')
		);

		add_settings_section(
			'sgi_ltrav_gravatar',
			__('Gravatar Options','letter-avatars'),
			array(&$this, 'gravatar_section_callback'),
			'sgi-letter-avatars'
		);

		add_settings_field(
			'sgi_ltrav_opts_gravatar',
			__('Use Gravatar','letter-avatars'),
			array(&$this, 'gravatar_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_gravatar',
			$this->opts['use_gravatar']
		);

		add_settings_section(
			'sgi_ltrav_style',
			__('Style Options','letter-avatars'),
			array(&$this, 'style_section_callback'),
			'sgi-letter-avatars'
		);

		add_settings_field(
			'sgi_ltrav_style_randomize',
			__('Randomize colors','letter-avatars'),
			array(&$this, 'randomize_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_style',
			$this->opts['style']
		);

		add_settings_field(
			'sgi_ltrav_style_lock',
			__('Lock color to user','letter-avatars'),
			array(&$this, 'lock_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_style',
			$this->opts['style']
		);

		add_settings_field(
			'sgi_ltrav_style_bg_color',
			__('Background color','letter-avatars'),
			array(&$this, 'bg_color_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_style',
			$this->opts['style']
		);

		add_settings_field(
			'sgi_ltrav_style_fg_color',
			__('Font color','letter-avatars'),
			array(&$this, 'font_color_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_style',
			$this->opts['style']
		);

		add_settings_section(
			'sgi_ltrav_gfont',
			__('Google Fonts options','letter-avatars'),
			array(&$this, 'gfont_section_callback'),
			'sgi-letter-avatars'
		);

		add_settings_field(
			'sgi_ltrav_style_load_gfont',
			__('Load Fonts','letter-avatars'),
			array(&$this, 'load_gfont_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_gfont',
			$this->opts['font']
		);

		add_settings_field(
			'sgi_ltrav_style_gfont_select',
			__('Font Family','letter-avatars'),
			array(&$this, 'gfont_select_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_gfont',
			$this->opts['font']
		);

		add_settings_field(
			'sgi_ltrav_style_gfont_size',
			__('Font size','letter-avatars'),
			array(&$this, 'font_size_callback'),
			'sgi-letter-avatars',
			'sgi_ltrav_gfont',
			$this->opts['font']
		);
		
	}

	/**
	 * Function that displays the gravatar section info
	 * @author Sibin Grasic
	 * @since 2.0
	 */
	public function gravatar_section_callback()
	{
		printf(
			'<p>%s</p>',
			__('This option controls gravatar behaviour when Letter Avatars are enabled')
		);
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

		printf(
			'<label for="sgi_ltrav_opts[use_gravatar]">
				<input type="checkbox" name="sgi_ltrav_opts[use_gravatar]" %s> %s
			</label>
			<p class="description">%s</p>',
			checked($use_gravatar,true,false),
			__('Show gravatar if available','letter-avatars'),
			__('If you check this option, Gravatar™ will be shown for those users that have it.','letter-avatars')
		);

	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function style_section_callback()
	{

		printf(
			'<p>%s</p>',
			__('These options control display options for Letter Avatars')
		);

	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function randomize_callback($style_opts)
	{

		printf(
			'<label for="sgi_ltrav_opts[style][rand_color]">
				<input class="randomize" type="checkbox" name="sgi_ltrav_opts[style][rand_color]" %s> %s
			</label>
			<p class="description">%s</p>',
			checked($style_opts['rand_color'],true, false),
			__('Randomize colors for each avatar','letter-avatars'),
			__('If you check this option, Each avatar will have unique background and font color.','letter-avatars')
		);

	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function lock_callback($style_opts)
	{

		printf(
			'<label class="random-lock" for="sgi_ltrav_opts[style][lock_color]">
				<input type="checkbox" name="sgi_ltrav_opts[style][lock_color]" %s> %s
			</label>
			<p class="description">%s</p>',
			checked($style_opts['lock_color'],true, false),
			__('Use a unique color for each user','letter-avatars'),
			__('If you check this option, Same color will be used for all comments by the same user','letter-avatars')
		);

	}

	/**
	 * 
	 * @since 2.0
	 * @return type
	 */
	public function bg_color_callback($style_opts)
	{

		printf(
			'<input id="ltrav-bg-color" type="text" name="sgi_ltrav_opts[style][bg_color]" value="%s">
			<p class="description">%s</p>',
			$style_opts['bg_color'],
			__('Background color for the avatar','letter-avatars')
		);

	}

	/**
	 * 
	 * @since 2.0
	 * @return type
	 */
	public function font_color_callback($style_opts)
	{

		printf(
			'<input id="ltrav-bg-color" type="text" name="sgi_ltrav_opts[style][color]" value="%s">
			<p class="description">%s</p>',
			$style_opts['color'],
			__('Font color for the avatar','letter-avatars')
		);

	}

	/**
	 * Function that displays the gravatar section info
	 * @author Sibin Grasic
	 * @since 2.0
	 */
	public function gfont_section_callback()
	{
		printf(
			'<p>%s</p>',
			__('These options control google fonts loading and selection')
		);
	}

	/**
	 * @since 2.0
	 * @return type
	 */
	public function load_gfont_callback($font_opts)
	{

		printf(
			'<label class="gfont-lock" for="sgi_ltrav_opts[font][load_gfont]">
				<input type="checkbox" name="sgi_ltrav_opts[font][load_gfont]" %s> %s
			</label>
			<p class="description">%s</p>',
			checked($font_opts['load_gfont'],true, false),
			__('Load Google font','letter-avatars'),
			__('If you check this option, selected Google Font will be added to your website','letter-avatars')
		);

	}

	/**
	 * @param type $font_opts 
	 * @since 2.0
	 * @return type
	 */
	public function gfont_select_callback($font_opts)
	{

		echo '<div style="display:block">';

		printf (
			'%s
			<p class="description">%s</p>',
			$this->generate_gfont_select($font_opts['font_name'],$font_opts['gfont_style']),
			__('Select font family and font style for letter avatar display','letter-avatars')
		);

		echo '</div>';

	}

	public function font_size_callback($font_opts)
	{

		printf(
			'<input type="number" class="small-text" name="sgi_ltrav_opts[font][font_size]" value="%s">
			<p class="description">%s</p>',
			$font_opts['font_size'],
			__('Font size - in px','letter-avatars')
		);

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
		$html = '';

		if (!$font_list) 
			return sprintf(
				'<strong>%s</strong>',
				__('Something went wrong, unable to fetch font list','letter-avatars')
			);
		

		$sel_font_array = null;

		$html .= '<select id="ltrav-gfont-select" name="sgi_ltrav_opts[font][font_name]">';

		foreach ($font_list['items'] as $font ):

			if ($selected_font == $font['family']) :
				$sel_font_array = $font;
			endif;

			$html .= sprintf (
				'<option value="%s" data-var="%s" %s>
					%s
				</option>',
				$font['family'],
				implode(',',$font['variants']),
				selected( $selected_font, $font['family'], false ),
				$font['family']

			);

		endforeach;

		$html .= '/<select>';

		$html .= '<select id="ltrav-gfont-style" name="sgi_ltrav_opts[font][gfont_style]>';

		foreach ($sel_font_array['variants'] as $variant) :

			$html .= sprintf(
				'<option value="%s" %s>%s</option>',
				$variant,
				selected($selected_style,$variant,false),
				$variant
			);

		endforeach;

		$html .= '</select><br>';

		return $html;

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

		if ( isset($opts['style']['lock_color']) ) :
			$opts['style']['lock_color'] = true;
		else :
			$opts['style']['lock_color'] = false;
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
		if ( ($hook !== 'options-general.php') && ($_GET['page'] !== 'sgi-letter-avatars') )
			return;

		wp_register_style( 'ltrav-admin-chosen', plugins_url('assets/css/chosen.min.css',SGI_LTRAV_BASENAME), null, SGI_LTRAV_VERSION );

		wp_enqueue_style('ltrav-admin-chosen');
		wp_enqueue_style('wp-color-picker');

		wp_register_script( 'ltrav-admin-chosen-js', plugins_url( "assets/js/chosen.jquery.min.js", SGI_LTRAV_BASENAME ), array('jquery'), SGI_LTRAV_VERSION, true);
		wp_register_script( 'ltrav-admin-js', plugins_url( "assets/js/ltrav-admin.js", SGI_LTRAV_BASENAME ), array('jquery', 'wp-color-picker'), SGI_LTRAV_VERSION, true);

		wp_enqueue_script('ltrav-admin-chosen-js');
		wp_enqueue_script('ltrav-admin-js');
	}

}