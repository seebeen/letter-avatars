<?php

namespace SGI\LtrAv\Core;

use \SGI\LtrAv\Admin as Admin;
use \SGI\LtrAv\Frontend as Frontend;

use const \SGI\LtrAv\FILE;
use const \SGI\LtrAv\PATH;

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
			\SGI\LtrAv\DOMAIN,
			false,
			$domain_path
		);

	}

	public function load_admin()
	{

        require_once (PATH . '/lib/admin/Utils.php');
		require_once (PATH . '/lib/admin/Core.php');
		require_once (PATH . '/lib/admin/Scripts.php');
		require_once (PATH . '/lib/admin/Settings.php');

		new Admin\Core();
		new Admin\Scripts();
		new Admin\Settings();

	}

	public function load_frontend()
	{

        require_once (PATH . '/lib/frontend/Utils.php');
        require_once (PATH . '/lib/frontend/Core.php');
        require_once (PATH . '/lib/frontend/Scripts.php');
        require_once (PATH . '/lib/frontend/Engine.php');

        new Frontend\Core();

	}

}