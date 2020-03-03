<?php

namespace SGI\LtrAv\Admin;

use const \SGI\LtrAv\BASENAME,
          \SGI\LtrAv\VERSION,
          \SGI\LtrAv\DOMAIN,
          \SGI\LtrAv\PATH;

/**
 * Main bakend class for all WP-Admin hooks and gimmicks
 * 
 * This class loads all of our backend hooks and sets up admin interfaces
 * 
 * @subpackage Admin Interfaces
 * @author Sibin Grasic
 * @since 3.0
 */
class Core
{

	/**
	 * @var version - plugin version
	 */
	private $version;


	/**
	 * Letter Avatars Admin constructor
	 * 
	 * Constructor first checks if plugin version exists in DB. If this is the first activation, plugin adds version info to the DB with autoload option set to false
	 * In that manner we can easily change plugin options, and add further defaults across versions, and preserve compatibility
	 * @return void
	 * @author Sibin Grasic
	 * @since 3.0
	 */
	public function __construct()
	{

		if ($ltrav_ver = get_option('sgi_ltrav_ver')) :

			if (version_compare(VERSION, $ltrav_ver, '>')) :
				update_option('sgi_ltrav_ver', VERSION);
			endif;

			$this->version = VERSION;

		else :

			$ltrav_ver = VERSION;
			add_option('sgi_ltrav_ver', $ltrav_ver, '', 'no');

		endif;

		// Add action link
		add_filter(
			'plugin_action_links_'.BASENAME,
			[&$this,'add_settings_link'],
			20,
			1
		);

	}

	public function add_settings_link($links)
	{

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			admin_url('options-general.php?page=sgi-letter-avatars'),
			__('Settings',DOMAIN)
		);

		return $links;

	}

}