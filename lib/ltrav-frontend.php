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

	private $locked_colors;

	private $used_colors;

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

		$this->used_colors = $this->locked_colors = [];

		/**
		 * @since 1.1
		 * @param boolean - boolean flag which determines if we should load inline styles
		 */
		$this->load_css = apply_filters('sgi/ltrav/load_styles',true);

		//add styles
		add_action('wp_head',array(&$this,'add_inline_styles'),20);
		add_action('wp_enqueue_scripts', array(&$this,'add_gfont_css'),20);

		add_filter('pre_get_avatar',array(&$this,'make_letter_avatar'),10,3);
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

	function process_user_identifier($id_or_email)
	{
		if ( is_numeric( $id_or_email ) ) :

			$user = get_user_by( 'id', absint( $id_or_email ) );
			$email = get_userdata($user->ID)->user_email;

		elseif ( is_string( $id_or_email ) ) :

			if ( strpos( $id_or_email, '@md5.gravatar.com' ) ) :
				// md5 hash
				list( $email_hash ) = explode( '@', $id_or_email );

				return false;

			else :
				
				$email = $id_or_email;
				
			endif;

		elseif ( $id_or_email instanceof WP_User ) :
			
			$user = $id_or_email;
			$email = get_userdata($user->ID)->user_email;

		elseif ( $id_or_email instanceof WP_Post ) :
			
			$user = get_user_by( 'id', (int) $id_or_email->post_author );
			$email = get_userdata($user->ID)->user_email;

		elseif ( $id_or_email instanceof WP_Comment ) :

			if ( ! empty( $id_or_email->user_id ) ) :

				$user = get_user_by( 'id', (int) $id_or_email->user_id );
				$email = get_userdata($user->ID)->user_email;

			endif;

			if ( ( ! $user || is_wp_error( $user ) ) && ! empty( $id_or_email->comment_author_email ) ) :

				$email = $id_or_email->comment_author_email;

			endif;

		endif;

		return $email;
	}

	function validate_gravatar($id_or_email, $args) {

		$email_hash = '';
		$user = $email = false;

		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
			$id_or_email = get_comment( $id_or_email );
		}

		// Process the user identifier.
		$email = $this->process_user_identifier($id_or_email);

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

		$response = wp_remote_head($url);
	        
        if( is_wp_error($response) ) :
            $data = 'not200';
        else :
            $data = $response['response']['code'];
        endif;
	    
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

	    if ($data == 200) :
	        return true;
	    else :
	  		return false;
	    endif;

	}

	public function add_gfont_css()
	{

		if (!$this->opts['font']['load_gfont'])
			return;

		if (!is_singular())
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
	 */
	public function make_letter_avatar($avatar, $id_or_email, $args )
	{

		global $comment;

		if ( is_admin() && !is_singular() && empty($comment))
			return $avatar;

		if ($this->validate_gravatar( $id_or_email, $args ) && $this->opts['use_gravatar'])
			return $avatar;

		$letter = mb_substr( $comment->comment_author, 0, 1 );

		$user_uid = $this->process_user_identifier($id_or_email);

		if ($this->opts['style']['rand_color']) :

			if ($this->opts['style']['lock_color'] && isset($this->locked_colors[$user_uid])) :

				$bg_color = $this->locked_colors[$user_uid];

			elseif ($this->opts['style']['lock_color']) :

				$bg_color = $this->generate_random_color();
				$this->locked_colors[$user_uid] = $bg_color;

			else :

				$bg_color = $this->generate_random_color();

			endif;

			$lt_color = $this->get_YIQ_contrast($bg_color);

		else :

			$bg_style = false;
			$lt_style = false;

		endif;

		$avatar = sprintf(
			'<div class="sgi-letter-avatar" style="line-height:%spx; height:%spx; width:%spx; %s">
				<span %s>%s</span>
			</div>',
			$args['height'],
			$args['height'],
			$args['width'],
			($bg_color) ? "background-color: {$bg_color};" : '',
			($lt_color) ? "style='color: {$lt_color};'" : '',
			$letter
		);

		return $avatar;

	}

	/**
	 * Description
	 * @since 2.0
	 * @return type
	 */
	private function generate_random_color()
	{

		$bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

		while (in_array($bg_color, $this->used_colors))
			$bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

		$this->used_colors[] = $bg_color;

		return $bg_color;

	}

	

}