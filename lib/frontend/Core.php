<?php

namespace SGI\LtrAv\Frontend;

class Core
{

    /**
     * @var Array - Plugin options
     * @since 1.0
     */
    private $opts;

    /**
     * @var bool - Flag which defines buddypress usage
     * @since 2.5
     */
    private $with_buddypress;

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
                    'load_gfont'   => true,
                    'use_css'      => true,
                    'font_name'    => 'Roboto',
                    'gfont_style'  => '',
                    'auto_size'    => true,
                    'font_size'    => '14',
                )
            )
        );

        $this->opts = $ltrav_opts;

        $with_bp = class_exists('BuddyPress');

        new Scripts(
            $with_bp,
            $ltrav_opts
        );

        new Engine(
            function_exists('has_wp_user_avatar'),
            $with_bp,
            $ltrav_opts
        );


    }

}