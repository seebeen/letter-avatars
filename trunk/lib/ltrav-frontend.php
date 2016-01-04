<?php

class SGI_LtrAv_Frontend
{
	private $opts;

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

		$this->opts = $ltrav_opts;

		$wp_version = get_bloginfo('version');

		if (version_compare($wp_version, '4.2','>=')) :
			add_filter('pre_get_avatar',array(&$this,'make_letter_avatar'),10,3);
		else :
			add_filter('get_avatar',array(&$this,'make_letter_avatar'),10,3);
		endif;

		//add styles
		add_action('wp_head',array(&$this,'add_inline_styles'),20);
		add_action('wp_enqueue_scripts', array(&$this,'add_gfont_css'),20);
	}

	private function get_YIQ_contrast($hexcolor)
	{
		$hexcolor = ltrim($hexcolor,'#');

		$r = hexdec(substr($hexcolor,0,2));
		$g = hexdec(substr($hexcolor,2,2));
		$b = hexdec(substr($hexcolor,4,2));

		$yiq = (($r*299)+($g*587)+($b*114))/1000;

		return ($yiq >= 128) ? '#000' : '#fff';
	}

	private function check_gravatar($id_or_email)
	{
		//id or email code borrowed from wp-includes/pluggable.php
	    $email = '';
	    if ( is_numeric($id_or_email) ) :

	            $id = (int) $id_or_email;
	            $user = get_userdata($id);

	            if ( $user ):
                    $email = $user->user_email;
                endif;

	    elseif ( is_object($id_or_email) ) :

            // No avatar for pingbacks or trackbacks
            $allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );

            if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) :
                return false;
            endif;

            if ( !empty($id_or_email->user_id) ) :

                $id = (int) $id_or_email->user_id;
                $user = get_userdata($id);

                if ( $user) :
                	$email = $user->user_email;
                endif;

            elseif ( !empty($id_or_email->comment_author_email) ) :

                $email = $id_or_email->comment_author_email;
            else :
            endif;
	    else :
            $email = $id_or_email;
	    endif;

	    $hashkey = md5(strtolower(trim($email)));
	    $protocol = is_ssl() ? 'https' : 'http';
	    $uri = $protocol.'://www.gravatar.com/avatar/' . $hashkey . '?d=404';

	    $data = wp_cache_get($hashkey);
	    if (false === $data) :

            $response = wp_remote_head($uri);
	        
	        if( is_wp_error($response) ) :
                $data = 'not200';
            else :
                $data = $response['response']['code'];
	        endif;
	        wp_cache_set($hashkey, $data, $group = '', $expire = 60*5);

	    endif;

	    if ($data == '200') :
            return true;
	    else :
      		return false;
	    endif;
		
	}

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

	public function make_letter_avatar($avatar,$id_or_email,$args)
	{
		global $comment;

		if ( !is_admin() && is_singular() && !empty( $comment ) ) :

			if ( !$this->check_gravatar( $id_or_email ) && $this->opts['use_gravatar'] ) :

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