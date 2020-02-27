<?php

namespace SGI\LtrAv\Frontend;

use const \SGI\LtrAv\PATH;

class Engine
{

    /**
     * @var Array - Plugin options
     * @since 1.0
     */
    private $opts;

    /**
     * @var boolean - Flag which determines if we should use caching for avatar checks
     * @since 2.6
     */
    private $use_cache;

    /**
     * @var bool - Flag which defines buddypress usage
     * @since 2.5
     */
    private $with_bp;

    /**
     * @var bool - Flag which defines if we're using WP User Avatar
     * @since 3.0
     */
    private $with_wpua;

    /**
     * @var string - Location to letter avatar template
     * @since 3.0
     */
    private $avatar_template;

    /**
     * @var Array - Already locked colors if user lock-in is enabled
     * @since 2.0
     */
    private $locked_colors;

    /**
     * @var array - Array of used colors for letter avatars
     * @since 2.0
     */
    private $used_colors;

    public function __construct($with_wpua, $with_buddypress, $opts)
    {

        $this->with_wpua   = $with_wpua;
        $this->with_bp     = $with_buddypress;
        $this->opts        = $opts;

        $this->used_colors   = [];
        $this->locked_colors = [];

        /**
         * @since 2.6
         * @param boolean - flag which determines if we should use caching for avatar checks
         */
        $this->use_cache = apply_filters('sgi/ltrav/use_cache', true);

        $this->avatar_template = file_get_contents(PATH . '/templates/letter_avatar.tpl');

        if ($this->with_wpua) :

            add_filter('wpua_get_avatar_filter',array(&$this,'override_wpua_avatar'),10,5);
            
        else :

            add_filter('pre_get_avatar',array(&$this,'override_avatar'),10,3);

        endif;

        if ($this->with_bp) :

            add_filter('bp_core_fetch_avatar',array(&$this,'override_bp_avatar'),10,9);

        endif;


    }

    /**
     * Function which overrides WP User Avatar
     * @param type $avatar 
     * @param type $id_or_email 
     * @param type $size 
     * @param type $default 
     * @param type $alt 
     * @since 3.0
     * @return type
     */
    public function override_wpua_avatar($avatar, $id_or_email, $size, $default, $alt)
    {
        if (function_exists('has_wp_user_avatar')) :

            if (has_wp_user_avatar($id_or_email))
                return $avatar;

            $args = array(
                'height'        => $size,
                'width'         => $size,
                'size'          => $size,
                'force_default' => 'y',
                'rating'        => 'x'
            );

            return $this->override_avatar($avatar,$id_or_email,$args);

        endif;

        return $avatar;

    }

    /**
     * Function that overrides BuddyPress avatar
     * @param type $html 
     * @param type $params 
     * @param type $item_id 
     * @param type $avatar_dir 
     * @param type $html_css_id 
     * @param type $html_width 
     * @param type $html_height 
     * @param type $avatar_folder_url 
     * @param type $avatar_folder_dir 
     * @since 2.5
     * @return string - Complete HTML for letter avatar, or original avatar if set
     */
    public function override_bp_avatar($html, $params, $item_id, $avatar_dir, $html_css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir)
    {
        
        $object = $params['object'];

        switch ($object) :

            case 'user' :

                if (empty($item_id) || $item_id == 0) :

                    if (is_user_logged_in()) :

                        $item_id = get_current_user_id();

                    else :

                        return $html;

                    endif;

                endif;

                $user = get_user_by('ID', $item_id);

                if ($user->first_name == '') :

                    $letter = mb_substr( $user->user_email, 0, 1 );

                else :

                    $letter = mb_substr( $user->first_name, 0, 1 );

                endif;

                if (validate_gravatar( $user, $params, $this->use_cache ) && $this->opts['use_gravatar'])
                    return $html;

                if (strpos($html, get_option('siteurl')) !== false)
                    return $html;

                return $this->make_letter_avatar(
                    $user->data->user_email,
                    $letter,
                    array(
                        'height' => $params['height'],
                        'width'  => $params['width']
                    )
                );

            break;

            case 'group' :

                if (strpos($html, 'mystery-group') === false)
                    return $html;

                $group = groups_get_group(array(
                    'group_id' => $item_id
                ));

                $letter = mb_substr($group->name, 0, 1);

                return $this->make_letter_avatar(
                    $group->name,
                    $letter,
                    array(
                        'height' => $params['height'],
                        'width'  => $params['width']
                    )
                );

            break;

        endswitch;


        return $html;
    }

    /**
     * 
     * Main plugin function which overrides the get_avatar call
     * @param string $avatar - HTML for the avatar
     * @param string $id_or_email - User ID or e-mail
     * @param array $args - Default arguments for avatar display
     * @author Sibin Grasic
     * @since 2.5
     * @return string - Avatar HTML
     */
    public function override_avatar($avatar, $id_or_email, $args)
    {

        global $comment;

        if ($id_or_email instanceof \WP_Comment) :

            $letter = mb_substr( $comment->comment_author, 0, 1 );

        elseif ( is_string($id_or_email) && is_email($id_or_email) ) :

            $letter = mb_substr( $id_or_email, 0, 1 );

        else :

            $user = get_user_by('ID', $id_or_email);

            if (!$user || ($user instanceof \WP_Error)) :

                $letter = mb_substr( $id_or_email, 0, 1 );

            else :

                if ($user->first_name == '') :

                    $letter = mb_substr( $user->user_email, 0, 1 );

                else :

                    $letter = mb_substr( $user->first_name, 0, 1 );

                endif;

            endif;

        endif;

        if ( is_admin() && !is_singular() && empty($comment) && !defined('DOING_AJAX'))
            return $avatar;

        if ($this->opts['use_gravatar'])
            if (validate_gravatar( $id_or_email, $args, $this->use_cache ))
                return $avatar;

        $user_uid = process_user_identifier($id_or_email);

        return $this->make_letter_avatar($user_uid, $letter, $args);

    }

    /**
     * Function that creates letter avatars
     * @param string $user_uid - user e-mail
     * @since 1.0
     */
    public function make_letter_avatar($user_uid, $letter, $args)
    {

        if ($this->opts['style']['rand_color']) :

            if ($this->opts['style']['lock_color'] && isset($this->locked_colors[$user_uid])) :

                $bg_color = $this->locked_colors[$user_uid];

            elseif ($this->opts['style']['lock_color']) :

                $bg_color = generate_pretty_random_color($user_uid, $this->used_colors);
                $this->locked_colors[$user_uid] = $bg_color;
                $this->used_colors[] = $bg_color;

            else :

                $bg_color = generate_pretty_random_color(false, $this->used_colors);

            endif;

            $lt_color = get_YIQ_contrast($bg_color);

        else :

            $bg_color = 'auto';
            $lt_color = 'auto';

        endif;

        $font_size = ($this->opts['font']['auto_size']) ? round($args['height'] * 0.75,0) : 'auto';

        $avatar_args = [
            '{{HEIGHT}}'      => $args['height'],
            '{{LINE_HEIGHT}}' => $args['height'],
            '{{WIDTH}}'       => $args['width'],
            '{{BG_COLOR}}'    => $bg_color,
            '{{COLOR}}'       => $lt_color,
            '{{FONT_SIZE}}'   => $font_size,
            '{{LETTER}}'      => $letter
        ];

        $avatar = strtr($this->avatar_template, $avatar_args);

        return $avatar;

    }



}