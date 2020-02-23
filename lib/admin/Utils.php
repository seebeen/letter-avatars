<?php

namespace SGI\LtrAv\Admin;

 /**
 * Function that gets the complete google fonts list.
 * We first check if we have the font list in a transient. If not it will be fetched from google.
 * @param boolean $load_gfont - Boolean that defines if we're loading google font
 * @param boolean $use_css - Boolean that defines if we're printing google font CSS
 * @return array - JSON decoded font list from google server
 * @author Sibin Grasic
 * @since 1.0
 */
function get_google_fonts($load_gfont, $use_css)
{

    if (!$load_gfont && !$use_css)
        return [];

    $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyC2XWzS33ZIlkC17s5GEX31ltIjOffyP5o';

    $font_list = get_transient('sgi_ltrav_gfonts');

    if ($font_list === false) :

        $font_list = wp_remote_get($url);

        if (!is_wp_error( $font_list )) :

            $font_list = $font_list['body'];

            set_transient('sgi_ltrav_gfonts', $font_list, (60*60*24));

            return json_decode($font_list,true);

        else :

            return false;

        endif;

    else :

        return json_decode($font_list, true);

    endif;

}

/**
 * Function that generates select boxes for google fonts.
 * We generate a select box for all the google fonts, with custom data-var variable that lists available styles for the font
 * 
 * @param string $selected_font - Selected font for the letter avatar
 * @param string $selected_style - Selected font style for the letter avatar
 * @param boolean $load_gfont - Boolean that defines if we're loading google font
 * @param boolean $use_css - Boolean that defines if we're printing google font CSS
 * @return void
 */
function generate_gfont_select($selected_font, $selected_style, $load_gfont, $use_css)
{

    $font_list = get_google_fonts($load_gfont, $use_css);
    $html = '';

    if (!$font_list && !$load_gfont && !$use_css)
        return sprintf(
            '<strong>%s</strong>',
            __('Google font list will be loaded when you check either of the above options','letter-avatars')
        );

    if (!$font_list) 
        return sprintf(
            '<strong>%s</strong>',
            __('Something went wrong, unable to fetch font list','letter-avatars')
        );
    

    $sel_font_array = null;

    $html .= '<select id="ltrav-gfont-select" name="sgi_ltrav_opts[font][font_name]"><option></option>';

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