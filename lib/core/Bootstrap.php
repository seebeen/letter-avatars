<?php

namespace SGI\LtrAv\Core;

use \SGI\LtrAv\Admin as Admin,
	\SGI\LtrAv\Frontend as Frontend;

use const \SGI\LtrAv\FILE,
          \SGI\LtrAv\PATH,
          \SGI\LtrAv\DOMAIN;

class Bootstrap
{

	public function __construct()
	{

		add_action('wp_loaded', [&$this, 'load_textdomain']);

		if (is_admin()) :
			add_action('plugins_loaded', [&$this, 'load_admin']);
		else :
			add_action('init', [&$this, 'load_frontend']);
		endif;

	}

	public function load_textdomain()
	{

		$domain_path = basename(dirname(FILE)).'/languages';

		load_plugin_textdomain(
			DOMAIN,
			false,
			$domain_path
		);

	}

	public function load_admin()
	{

		new Admin\Core();
		new Admin\Scripts();
		new Admin\Settings();

	}

	public function load_frontend()
	{

	    new Frontend\Core();

	}

}