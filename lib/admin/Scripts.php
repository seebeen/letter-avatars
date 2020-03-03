<?php

namespace SGI\LtrAv\Admin;

use const \SGI\LtrAv\BASENAME,
          \SGI\LtrAv\VERSION;

class Scripts
{

	public function __construct()
	{

		add_action('admin_enqueue_scripts',[&$this,'load_css'], 50, 1);
		add_action('admin_enqueue_scripts',[&$this,'load_js'], 50, 1);

	}

	public function load_css($hook)
	{

		if ( $hook !== 'settings_page_sgi-letter-avatars' )
			return;

		wp_register_style( 'ltrav-admin-select2', plugins_url('assets/css/select2.min.css', BASENAME), null, VERSION );

		wp_enqueue_style('ltrav-admin-select2');
		wp_enqueue_style('wp-color-picker');

	}

	public function load_js($hook)
	{

		if ( $hook !== 'settings_page_sgi-letter-avatars' )
			return;

		wp_register_script( 'ltrav-admin-select2-js', plugins_url( "assets/js/select2.min.js", BASENAME ), ['jquery'], VERSION, true);
		wp_register_script( 'ltrav-admin-js', plugins_url( "assets/js/ltrav-admin.js", BASENAME ), ['jquery', 'wp-color-picker'], VERSION, true);

		wp_enqueue_script('ltrav-admin-select2-js');
		wp_enqueue_script('ltrav-admin-js');

	}



}