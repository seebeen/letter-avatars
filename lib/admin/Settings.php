<?php

namespace SGI\LtrAv\Admin;

use const \SGI\LtrAv\DOMAIN;


class Settings
{

    /**
     * @var opts - plugin opts
     */
    private $opts;


    public function __construct()
    {

        $ltrav_opts = get_option(
            'sgi_ltrav_opts',
            array(
                'use_gravatar' => true,
                'style'        => array(
                    'case'         => 'uppercase',
                    'shape'        => 'round',
                    'rand_color'   => false,
                    'lock_color'   => false,
                    'color'        => '#FFF',
                    'bg_color'     => '#000',
                ),
                'font'         => array(
                    'load_gfont'   => false,
                    'use_css'      => false,
                    'font_name'    => 'Roboto',
                    'gfont_style'  => '',
                    'auto_size'    => true,
                    'font_size'    => '14',
                )
        ));

        $this->opts = $ltrav_opts;

        add_action('admin_init', [&$this,'register_settings']);
        add_action('admin_menu', [&$this, 'add_settings_menu']);

    }

    public function add_settings_menu()
    {

        add_submenu_page(
            'options-general.php',
            __('Letter Avatars', DOMAIN),
            __('Letter Avatars', DOMAIN),
            'manage_options',
            'sgi-letter-avatars',
            [&$this, 'settings_callback']
        );

    }

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
            'sgi_ltrav_settings_font_case',
            __('Font Style','letter-avatars'),
            array(&$this, 'style_case_callback'),
            'sgi-letter-avatars',
            'sgi_ltrav_style',
            $this->opts['style']
        );

        add_settings_field(
            'sgi_ltrav_settings_shape',
            __('Avatar shape', 'letter-avatars'),
            array(&$this, 'shape_callback'),
            'sgi-letter-avatars',
            'sgi_ltrav_style',
            $this->opts['style']
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
            'sgi_ltrav_style_use_css',
            __('Load CSS','letter-avatars'),
            array(&$this, 'use_css_callback'),
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
     * @since 2.8
     * @return type
     */
    public function style_case_callback($style_opts)
    {

        if (!array_key_exists('case',$style_opts))
            $style_opts['case'] = 'uppercase';

        printf(
            '<select name="sgi_ltrav_opts[style][case]">
                <option value="uppercase" %s>%s</option>
                <option value="lowercase" %s>%s</option>
            </select>
            <p class="description">%s</p>',
            selected('uppercase', $style_opts['case'], false),
            __('Uppercase','letter-avatars'),
            selected('lowercase', $style_opts['case'], false),
            __('Lowercase','letter-avatars'),
            __('Select case for your letter avatar. Upper or lower','letter-avatars')
        );

    }

    public function shape_callback($style_opts)
    {

        if (!array_key_exists('shape',$style_opts))
            $style_opts['shape'] = 'square';

        printf(
            '<select name="sgi_ltrav_opts[style][shape]">
                <option value="square" %s>%s</option>
                <option value="round" %s>%s</option>
            </select>
            <p class="description">%s</p>',
            selected('square', $style_opts['shape'], false),
            __('Square','letter-avatars'),
            selected('round', $style_opts['shape'], false),
            __('Round','letter-avatars'),
            __('Select shape for your letter avatar. Sqare or round','letter-avatars')
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
     * 
     * @param array $font_opts 
     * @since 2.2
     * @return void
     */
    public function use_css_callback($font_opts)
    {

        printf(
            '<label class="css-lock" for="sgi_ltrav_opts[font][use_css]">
                <input type="checkbox" name="sgi_ltrav_opts[font][use_css]" %s> %s
            </label>
            <p class="description">%s</p>',
            checked($font_opts['use_css'],true, false),
            __('Use Google Font CSS','letter-avatars'),
            __('If you check this option, Google Font CSS will be printed even if you don\'t load Google Font','letter-avatars')
        );

    }

    /**
     * @param type $font_opts 
     * @since 2.0
     * @return type
     */
    public function gfont_select_callback($font_opts)
    {

        if (!array_key_exists('font_name', $font_opts))
            $font_opts['font_name'] = 'Roboto';

        echo '<div style="display:block">';

        printf (
            '%s
            <p class="description">%s</p>',
            generate_gfont_select(
                $font_opts['font_name'],
                $font_opts['gfont_style'],
                $font_opts['load_gfont'],
                $font_opts['use_css']
            ),
            __('Select font family and font style for letter avatar display','letter-avatars')
        );

        echo '</div>';

    }

    public function font_size_callback($font_opts)
    {

        printf(
            '<label for="sgi_ltrav_opts[font][auto_size]">
                <input class="font-size-lock" type="checkbox" name="sgi_ltrav_opts[font][auto_size]" %s> %s
            </label>
            <p class="description">%s</p>',
            checked($font_opts['auto_size'],true, false),
            __('Automatic Font Size','letter-avatars'),
            __('If you check this option, font size will be determined automatically by avatar size','letter-avatars')
        );

        printf(
            '<div style="margin-top:10px; display: %s" class="hide-if-auto">
                <input type="number" class="small-text" name="sgi_ltrav_opts[font][font_size]" value="%s">
                <p class="description">%s</p>
            </div>',
            ($font_opts['auto_size']) ? 'none' : 'block',
            $font_opts['font_size'],
            __('Font size - in px','letter-avatars')
        );

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

        if (isset($opts['font']['use_css'])) :
            $opts['font']['use_css'] = true;
        else :
            $opts['font']['use_css'] = false;
        endif;

        if (isset($opts['font']['auto_size'])) :
            $opts['font']['auto_size'] = true;
        else :
            $opts['font']['auto_size'] = false;
        endif;

        if ($opts['font']['gfont_style'] == '') :
            $opts['font']['gfont_style'] = 'regular';
        endif;

        return $opts;
    }

}