<?php defined('ABSPATH') or die('No script kiddies please!');
/**
 * Plugin Name: SpeedCounter
 * Description: A simple visitor counter for your blog.
 * Version: 1.0.0
 * Author: SpeedCounter.net
 * Author URI: https://speedcounter.net/
 * License: GPL2
 */

/*  Copyright 2015  Günter Grodotzki  (email : gunter@grodotzki.co.za)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('widgets_init', function(){
    register_widget('SpeedCounter_Widget');
});

class SpeedCounter_Widget extends WP_Widget {

    protected static $colors = [
        'brightgreen',
        'green',
        'yellow',
        'yellowgreen',
        'orange',
        'red',
        'blue',
        'gray',
        'lightgray',
        'pink',
    ];

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        parent::__construct(
            'speedcounter_widget',
            'SpeedCounter',
            ['description' => 'A simple visitor counter for your blog.']
        );

    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance) {

        // get user_id
        $user_id = get_option('speedcounter_user_id');
        if (empty($user_id)) {
            $user_id = $this->createUserId();
            if (!empty($user_id)) {
                add_option('speedcounter_user_id', $user_id);
            }
        }

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
        ?>
        <div class="speedcounter">
            <?php if ($instance['show_today'] == 'on'): ?>
            <div class="speedcounter-tag">
                <?php echo $this->createTag($user_id, $instance['color'], 'today'); ?>
            </div>
            <?php endif; ?>
            <?php if ($instance['show_yesterday'] == 'on'): ?>
            <div class="speedcounter-tag">
                <?php echo $this->createTag($user_id, $instance['color'], 'yesterday'); ?>
            </div>
            <?php endif; ?>
            <?php if ($instance['show_total'] == 'on'): ?>
            <div class="speedcounter-tag">
                <?php echo $this->createTag($user_id, $instance['color'], 'total'); ?>
            </div>
            <?php endif; ?>
            <?php if ($instance['show_online'] == 'on'): ?>
            <div class="speedcounter-tag">
                <?php echo $this->createTag($user_id, $instance['color'], 'online'); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance) {
        $instance = $this->defaults($instance);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('color'); ?>"><?php _e( 'Color:' ); ?></label>
            <select name="<?php echo $this->get_field_name('color'); ?>" id="<?php echo $this->get_field_id('color'); ?>" class="widefat">
            <?php foreach (self::$colors as $color): ?>
                <option value="<?php _e($color); ?>"<?php selected($instance['color'], $color); ?>><?php _e($color); ?></option>
            <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('show_today'); ?>" name="<?php echo $this->get_field_name('show_today'); ?>" type="checkbox"<?php checked($instance['show_today'], 'on'); ?>>
            <label for="<?php echo $this->get_field_id('show_today'); ?>"><?php _e('Show today counter'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('show_yesterday'); ?>" name="<?php echo $this->get_field_name('show_yesterday'); ?>" type="checkbox"<?php checked($instance['show_yesterday'], 'on'); ?>>
            <label for="<?php echo $this->get_field_id('show_yesterday'); ?>"><?php _e('Show yesterday counter'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('show_total'); ?>" name="<?php echo $this->get_field_name('show_total'); ?>" type="checkbox"<?php checked($instance['show_total'], 'on'); ?>>
            <label for="<?php echo $this->get_field_id('show_total'); ?>"><?php _e('Show total counter'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('show_online'); ?>" name="<?php echo $this->get_field_name('show_online'); ?>" type="checkbox"<?php checked($instance['show_online'], 'on'); ?>>
            <label for="<?php echo $this->get_field_id('show_online'); ?>"><?php _e('Show online counter'); ?></label>
        </p>
        <?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update($new_instance, $old_instance) {
        $instance = [];

        $instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';

        if (empty($new_instance['color']) || !in_array($new_instance['color'], self::$colors)) {
            $instance['color'] = self::$colors[0];
        } else {
            $instance['color'] = $new_instance['color'];
        }

        $instance['show_today']     = !empty($new_instance['show_today']) ?     $new_instance['show_today']     : 'off';
        $instance['show_yesterday'] = !empty($new_instance['show_yesterday']) ? $new_instance['show_yesterday'] : 'off';
        $instance['show_total']     = !empty($new_instance['show_total']) ?     $new_instance['show_total']     : 'off';
        $instance['show_online']    = !empty($new_instance['show_online']) ?    $new_instance['show_online']    : 'off';

        return $instance;
    }

    /**
     * apply defaults
     * @param array $instance
     * @return array
     */
    protected function defaults(array $instance) {
        $instance['title']          = !empty($instance['title']) ?          $instance['title'] : '';
        $instance['color']          = !empty($instance['color']) ?          $instance['color'] : self::$colors[0];
        $instance['show_today']     = !empty($instance['show_today']) ?     $instance['show_today'] : 'on';
        $instance['show_yesterday'] = !empty($instance['show_yesterday']) ? $instance['show_yesterday'] : 'on';
        $instance['show_total']     = !empty($instance['show_total']) ?     $instance['show_total'] : 'on';
        $instance['show_online']    = !empty($instance['show_online']) ?    $instance['show_online'] : 'on';
        return $instance;
    }

    /**
     * create html img-tag
     * @param int $id
     * @param string $color
     * @param string $field
     */
    protected function createTag($user_id, $color, $field) {
        return sprintf(
            '<img src="https://speedcounter.net/counter-v2/%d-%s-%s.svg" alt="visitors %s">',
            (int) $user_id, urlencode((string) $field), urlencode((string) $color), esc_html((string) $field)
        );
    }

    /**
     * create user id via api
     * @return boolean|number
     */
    protected function createUserId() {
        $response = wp_remote_get('https://speedcounter.net/api', ['httpversion' => '1.1']);
        if (is_wp_error($response)) {
            return false;
        }

        if ($response['response']['code'] !== 200) {
            return false;
        }

        $json = json_decode($response['body'], true);
        if (!is_array($json) || empty($json['user_id'])) {
            return false;
        }

        return (int) $json['user_id'];
    }

}