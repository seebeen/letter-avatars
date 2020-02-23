<?php

namespace SGI\LtrAv\Frontend;

class Scripts
{

    /**
     * @var Array - Plugin options
     * @since 1.0
     */
    private $opts;

    /**
     * @var bool - Defines if we're loading styles
     * @since 1.1
     */
    private $load_css;

    /**
     * @var bool - Flag which defines buddypress usage
     * @since 2.5
     */
    private $with_bp;

    public function __construct($with_bp, $opts)
    {

        $this->with_bp = $with_bp;
        $this->opts    = $opts;

        /**
         * @since 1.1
         * @param boolean - boolean flag which determines if we should load inline styles
         */
        $this->load_css = apply_filters('sgi/ltrav/load_styles', true);

        //add styles
        add_action('wp_head',[&$this,'add_inline_styles'],20);
        add_action('wp_enqueue_scripts', [&$this,'add_gfont_css'],20);

    }

    public function add_inline_styles()
    {

        if (!$this->load_css)
            return;

        global $post;

        if (!is_singular())
            return;

        $css = generate_css($this->opts['style'],$this->opts['font'],$this->with_bp);

        echo "<style>${css}</style>";

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

}