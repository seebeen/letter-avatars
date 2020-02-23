<?php

namespace SGI\LtrAv\Frontend;

use const \SGI\LtrAv\PATH;

/**
 * Function that generates inline style for the plugin
 * @param array $style_opts - Style options
 * @param array $font_opts  - Font options
 * @return string - Compiled css for the plugin
 * @author Sibin Grasic
 * @since 1.0
 */
function generate_css($style_opts,$font_opts, $with_bp)
{

    $gfont_style = $font_opts['gfont_style'];
    $gfont_style = ($gfont_style == 'regular') ? '400' : $gfont_style;
    $gfont_style = ($gfont_style == 'italic') ? '400italic' : $gfont_style;

    if (strlen($gfont_style) > 3) :

        $weight = substr($gfont_style, 0, 3);
        $style = substr($gfont_style, 3);

    else :

        $weight = $gfont_style;
        $style  = 'regular';

    endif;


    $css_template = file_get_contents(PATH . '/templates/inline-css.css');

    $css_opts = [
        '{{LTRAV_BORDER_RADIUS}}' => ($style_opts['shape'] == 'round') ? '50%' : '0',
        '{{LTRAV_BG}}'            => ($style_opts['rand_color']) ? '' : $style_opts['bg_color'],  
        '{{LTRAV_COLOR}}'         => ($style_opts['rand_color']) ? '' : $style_opts['color'],
        '{{LTRAV_FONT_CASE}}'     => $style_opts['case'],
        '{{LTRAV_FONT_FAMILY}}'   => $font_opts['font_name'],
        '{{LTRAV_FONT_WEIGHT}}'   => $weight,
        '{{LTRAV_FONT_STYLE}}'    => $style,
        '{{LTRAV_FONT_SIZE}}'     => ($font_opts['auto_size']) ? '' : "{$font_opts['font_size']}px",
    ];

    $css = strtr($css_template, $css_opts);
    $css = preg_replace('/[\n\r].+: ;/i', '', $css);

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
function get_YIQ_contrast($hexcolor)
{
    $hexcolor = ltrim($hexcolor,'#');

    $r = hexdec(substr($hexcolor,0,2));
    $g = hexdec(substr($hexcolor,2,2));
    $b = hexdec(substr($hexcolor,4,2));

    $yiq = (($r*299)+($g*587)+($b*114))/1000;

    return ($yiq >= 128) ? '#000' : '#fff';
}

/**
 * @since 2.1
 * @param int $H - Hue
 * @param int $S - Saturation
 * @param int $V - Value
 * @return string - Hex color
 */
function hsl2rgb($H, $S, $V)
{

    $H *= 6;
    $h = intval($H);
    $H -= $h;
    $V *= 255;
    $m = $V*(1 - $S);
    $x = $V*(1 - $S*(1-$H));
    $y = $V*(1 - $S*$H);
    $a = array(
        array($V, $x, $m),
        array($y, $V, $m),
        array($m, $V, $x),
        array($m, $y, $V),
        array($x, $m, $V),
        array($V, $m, $y)
    );

    $a = $a[$h];

    return sprintf("#%02X%02X%02X", $a[0], $a[1], $a[2]);

}

/**
 * @since 2.1
 * @param string $user_uid 
 * @return string - hex color for avatar background
 */
function generate_pretty_random_color($user_uid = false, $used_colors)
{

    $user_uid = ($user_uid) ? $user_uid : uniqid();

    $hue = unpack('L', hash('adler32', strtolower($user_uid), true))[1];

    do {

        $bg_color = hsl2rgb($hue/0xFFFFFFFF, (mt_rand() / mt_getrandmax()), 1);

    } while (in_array($bg_color, $used_colors, true));

    return $bg_color;

}

/**
 * Description
 * @since 2.0
 * @return type
 */
function generate_random_color()
{

    $bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

    while (in_array($bg_color, $this->used_colors))
        $bg_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

    $this->used_colors[] = $bg_color;

    return $bg_color;

}

function validate_gravatar($id_or_email, $args, $use_cache) {

    $email_hash = '';
    $user = $email = false;

    if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
        $id_or_email = get_comment( $id_or_email );
    }

    // Process the user identifier.
    $email = process_user_identifier($id_or_email);

    if ( ! $email_hash ) {
        if ( $user ) {
            $email = $user->user_email;
        }

        if ( $email ) {
            $email_hash = md5( strtolower( trim( $email ) ) );
        }
    }

    //Cache usage check
    if (use_cache) :

        $data = wp_cache_get("ltrav_{$email_hash}",'sgi_ltrav');

    else :

        $data = false;

    endif;

    /*
    This is really important. If we're using object caching. Entire avatar check block with gravatar won't happen until cache expires.
    This shaves off a lot of load time on posts / pages with a lot of comments.
    */
    if ($data === false) : //Cache miss

        if ( $email_hash ) {
            $args['found_avatar'] = true;
            $gravatar_server = hexdec( $email_hash[0] ) % 3;
        } else {
            $gravatar_server = rand( 0, 2 );
        }

        if (!array_key_exists('size',$args))
            $args['size'] = 200;

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
            $data = 404;
        else :
            $data = $response['response']['code'];
        endif;
        
        if ($use_cache) :

            wp_cache_set("ltrav_{$email_hash}", $data, 'sgi_ltrav', 60*60*12);

        endif;
        
    endif;

    return ($data == 200) ? true : false;

}

/**
 * Description
 * @param type $id_or_email 
 * @since 2.1
 * @return type
 */
function process_user_identifier($id_or_email)
{

    $email = '';

    if ( $id_or_email instanceof \WP_Comment ) :

        if ( ! empty( $id_or_email->user_id ) ) :

            $user = get_user_by( 'id', (int) $id_or_email->user_id );
            $email = get_userdata($user->ID)->user_email;

        else :

            $user = $email = false;

        endif;

        if ( ( ! $user || is_wp_error( $user ) ) && ! empty( $id_or_email->comment_author_email ) ) :

            $email = $id_or_email->comment_author_email;

        endif;

    elseif ( is_numeric( $id_or_email ) ) :

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

    elseif ( $id_or_email instanceof \WP_User ) :
        
        $user = $id_or_email;
        $email = get_userdata($user->ID)->user_email;

    elseif ( $id_or_email instanceof \WP_Post ) :
        
        $user = get_user_by( 'id', (int) $id_or_email->post_author );
        $email = get_userdata($user->ID)->user_email;

    endif;

    if (!$email)
        unset($email);

    return ($email ?? 'A');

}