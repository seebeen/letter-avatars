<?php
/**
 * @package SGI/LTRAV
 */

/**
 * Main frontend class for the plugin
 * 
 * This class handles options loading and avatar overriding
 * @subpackage Frontend Interfaces
 * @author Sibin Grasic
 * @since 1.0
 * @var opts - plugin options
 * @var load_css - Flag that determins style loading
 */
class SGI_LtrAv_Frontend
{
	private $opts;
	private $load_css;

	/**
	 * Class Constructor
	 * @author Sibin Grasic
	 * @since 1.0
	 * @todo Check the pre_get_avatar filter that's very very buggy
	 * @return type
	 */
	public function __construct()
	{
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

		/**
		 * @since 1.1
		 * @param array - Plugin options that you want to filter
		 */
		$this->opts = apply_filters('sgi_ltrav_opts',$ltrav_opts);

		//$wp_version = get_bloginfo('version');

		add_filter('get_avatar',array(&$this,'make_letter_avatar'),10,6);

		/**
		 * @since 1.1
		 * @param boolean - boolean flag which determines if we should load inline styles
		 */
		$this->load_css = apply_filters('sgi_ltrav_load_styles',true);

		//add styles
		add_action('wp_head',array(&$this,'add_inline_styles'),20);
		add_action('wp_enqueue_scripts', array(&$this,'add_gfont_css'),20);
	}

	/**
	 * Function that deterimines the font color for the letter avatar based on the background color
	 * 
	 * @param string $hexcolor - Hex value for the background color
	 * @return string - color of the letter for the avatar
	 * @link https://en.wikipedia.org/wiki/YIQ
	 * @link https://24ways.org/2010/calculating-color-contrast/
	 * @author Sibin Grasic
	 * @since 1.0
	 * 
	 */
	private function get_YIQ_contrast($hexcolor)
	{
		$hexcolor = ltrim($hexcolor,'#');

		$r = hexdec(substr($hexcolor,0,2));
		$g = hexdec(substr($hexcolor,2,2));
		$b = hexdec(substr($hexcolor,4,2));

		$yiq = (($r*299)+($g*587)+($b*114))/1000;

		return ($yiq >= 128) ? '#000' : '#fff';
	}

	private function check_gravatar($id_or_email,$args)
	{
		$email_hash = '';
		$user = $email = false;

		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
			$id_or_email = get_comment( $id_or_email );
		}

		// Process the user identifier.
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );
		} elseif ( is_string( $id_or_email ) ) {
			if ( strpos( $id_or_email, '@md5.gravatar.com' ) ) {
				// md5 hash
				list( $email_hash ) = explode( '@', $id_or_email );
			} else {
				// email address
				$email = $id_or_email;
			}
		} elseif ( $id_or_email instanceof WP_User ) {
			// User Object
			$user = $id_or_email;
		} elseif ( $id_or_email instanceof WP_Post ) {
			// Post Object
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
		} elseif ( $id_or_email instanceof WP_Comment ) {
			/**
			 * Filter the list of allowed comment types for retrieving avatars.
			 *
			 * @since 3.0.0
			 *
			 * @param array $types An array of content types. Default only contains 'comment'.
			 */
			$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
				$args['url'] = false;
				/** This filter is documented in wp-includes/link-template.php */
				return apply_filters( 'get_avatar_data', $args, $id_or_email );
			}

			if ( ! empty( $id_or_email->user_id ) ) {
				$user = get_user_by( 'id', (int) $id_or_email->user_id );
			}
			if ( ( ! $user || is_wp_error( $user ) ) && ! empty( $id_or_email->comment_author_email ) ) {
				$email = $id_or_email->comment_author_email;
			}
		}

		if ( ! $email_hash ) {
			if ( $user ) {
				$email = $user->user_email;
			}

			if ( $email ) {
				$email_hash = md5( strtolower( trim( $email ) ) );
			}
		}

		if ( $email_hash ) {
			$args['found_avatar'] = true;
			$gravatar_server = hexdec( $email_hash[0] ) % 3;
		} else {
			$gravatar_server = rand( 0, 2 );
		}

		$url_args = array(
			's' => $args['size'],
			'd' => '404',
			'f' => $args['force_default'] ? 'y' : false,
			'r' => $args['rating'],
		);

		if ( is_ssl() ) {
			$url = 'https://secure.gravatar.com/avatar/' . $email_hash;
		} else {
			$url = sprintf( 'http://%d.gravatar.com/avatar/%s', $gravatar_server, $email_hash );
		}

		$url = add_query_arg(
			rawurlencode_deep( array_filter( $url_args ) ),
			set_url_scheme( $url, $args['scheme'] )
		);

	    $data = wp_cache_get($email_hash);
	    if (false === $data) :

            $response = wp_remote_head($url);
	        
	        if( is_wp_error($response) ) :
                $data = 'not200';
            else :
                $data = $response['response']['code'];
	        endif;
	        wp_cache_set($email_hash, $data, $group = '', $expire = 60*5);

	    endif;

	    if ($data == '200') :
            return true;
	    else :
      		return false;
	    endif;
		
	}

	/**
	 * Function that generates inline style for the plugin
	 * @param array $style_opts - Style options
	 * @param array $font_opts  - Font options
	 * @return string - Compiled css for the plugin
	 * @author Sibin Grasic
	 * @since 1.0
	 * @todo Improve and replace massive switch block for gfont options
	 */
	private function generate_css($style_opts,$font_opts)
	{

		$css = '';

		if (!$style_opts['rand_color']) :

			$css = 
			"
			.sgi-letter-avatar{
				background-color:{$style_opts['bg_color']};
			}
			.sgi-letter-avatar > span{
				color:{$style_opts['color']};
			}
			";
		endif;

		$padding = str_replace(' ','px ',$style_opts['padding']).'px';

		if ($font_opts['load_gfont']) :

			$gfont = "font-family:\"{$font_opts['font_name']}\";\n";

			switch ($font_opts['gfont_style']) :
				case '100' :
					$gfont .=
					"
					font-weight:100;\n
					";
					break;
				case '100italic' :
					$gfont .=
					"
					font-weight:100;\n
					font-style: italic;
					";
					break;
				case '300' :
					$gfont .=
					"
					font-weight:300;\n
					";
					break;
				case '300italic' :
					$gfont .=
					"
					font-weight:300;\n
					font-style: italic;
					";
					break;
				case 'regular' :
					$gfont .=
					"
					font-weight:400;
					";
					break;
				case 'italic' :
					$gfont .=
					"
					font-weight:400;
					font-style: italic;
					";
					break;
				case '500' :
					$gfont .=
					"
					font-weight:500;\n
					";
					break;
				case '500italic' :
					$gfont .=
					"
					font-weight:500;\n
					font-style: italic;
					";
					break;
				case '700' :
					$gfont .=
					"
					font-weight:700;\n
					";
					break;
				case '700italic' :
					$gfont .=
					"
					font-weight:700;\n
					font-style: italic;
					";
					break;
				case '900' :
					$gfont .=
					"
					font-weight:900;\n
					";
					break;
				case '900italic' :
					$gfont .=
					"
					font-weight:900;\n
					font-style: italic;
					";
					break;
			endswitch;

			

		else :

			$gfont = '';

		endif;

		$css .=
		"
		.sgi-letter-avatar{
			text-align:center;
		}
		.sgi-letter-avatar > span{
			display:block;
			padding:{$padding};
			font-size:{$font_opts['font_size']}px;
			{$gfont}
		}
		";

		return $css;
	}

	public function add_inline_styles()
	{	

		if (!$this->load_css)
			return;

		global $post;

		if (!is_singular())
			return;

		$css = $this->generate_css($this->opts['style'],$this->opts['font']);

		echo 
		"
		<style>
		${css}
		</style>
		";

	}

	public function add_gfont_css()
	{
		if (!is_singular() && !$this->opts['font']['load_gfont'])
			return;
		
		if (!$this->load_css)
			return;

		$font_name = str_replace(' ', '+', $this->opts['font']['font_name']);
		$font_style = $this->opts['font']['gfont_style'];

		if ($font_style == 'regular') :
			$font_style = '400';
		endif;

		if ($font_style == 'italic') :
			$font_style = '400italic';
		endif;

		wp_enqueue_style('sgi-letter-avatar-gfont', "//fonts.googleapis.com/css?family=${font_name}:${font_style}&subset=latin-ext", false, null );
	}

	/**
	 * Main plugin function which overrides the get_avatar call
	 * @param string $avatar - HTML for the avatar
	 * @param string $id_or_email - User ID or e-mail
	 * @param array $args - Default arguments for avatar display
	 * @return string - Avatar HTML
	 * @author Sibin Grasic
	 * @since 1.0
	 * @todo Improve avatar templating in 1.3
	 * @todo Improve HTML according to browser specs 1.4
	 * @todo Enable display options (for letters) 1.5
	 */
	public function make_letter_avatar($avatar, $id_or_email, $size, $default, $alt, $args )
	{
		global $comment;


		if ( !is_admin() && is_singular() && !empty($comment)) :

			if ( !$this->check_gravatar( $id_or_email, $args ) && $this->opts['use_gravatar'] ) :

				$letter = substr( $comment->comment_author, 0, 1 );

				if ($this->opts['style']['rand_color']) :

					$bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
					$lt_color = $this->get_YIQ_contrast($bg_color);

					$bg_style = "background-color:${bg_color};";
					$lt_style = "style=\"color:${lt_color}\"";
				else :

					$bg_style = '';
					$lt_style = '';

				endif;

				$avatar =
				"
				<div class=\"avatar sgi-letter-avatar\" style=\"width:{$args['width']}px; height:{$args['height']}px; ${bg_style}\">
					<span ${lt_style}>${letter}</span>
				</div>
				";

				return $avatar;

			else : 

				return $avatar;

			endif;

		endif;

		
	}

	

}