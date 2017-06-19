<?php

namespace Alekhin\FrontEndUser;

use Alekhin\FrontEndUser\Admin\menu as admenu;
use Alekhin\FrontEndUser\Admin\pages;
use Alekhin\FrontEndUser\Admin\settings;

class Menu {

    static function get_menu_item_login($parent_id = 0) {
        if (($page_id = pages::get_pages('login')) === 0) {
            return NULL;
        }

        $l = new \stdClass();
        $l->title = get_the_title($page_id);
        $l->attr_title = $l->title;
        $l->menu_item_parent = $parent_id;
        $l->ID = '';
        $l->url = pages::get_page_url('login');
        $l->db_id = $page_id;
        return $l;
    }

    static function get_menu_item_register($parent_id = 0) {
        if (($page_id = pages::get_pages('register')) === 0) {
            return NULL;
        }

        $l = new \stdClass();
        $l->title = get_the_title($page_id);
        $l->attr_title = $l->title;
        $l->menu_item_parent = $parent_id;
        $l->ID = '';
        $l->url = pages::get_page_url('register');
        $l->db_id = $page_id;
        return $l;
    }

    static function get_menu_item_recover($parent_id = 0) {
        if (($page_id = pages::get_pages('recover')) === 0) {
            return NULL;
        }

        $l = new \stdClass();
        $l->title = get_the_title($page_id);
        $l->attr_title = $l->title;
        $l->menu_item_parent = $parent_id;
        $l->ID = '';
        $l->url = pages::get_page_url('recover');
        $l->db_id = $page_id;
        return $l;
    }

    static function get_menu_item_wp_admin($parent_id = 0) {
        $l = new \stdClass();
        $l->title = 'WP Admin';
        $l->attr_title = 'WordPress administration dashboard';
        $l->menu_item_parent = $parent_id;
        $l->ID = '';
        $l->url = admin_url();
        $l->db_id = '';
        return $l;
    }

    static function get_menu_item_logout($parent_id = 0) {
        $l = new \stdClass();
        $l->title = 'Logout';
        $l->attr_title = 'Logout link';
        $l->menu_item_parent = $parent_id;
        $l->ID = '';
        $l->db_id = '';
        $l->url = wp_logout_url(home_url());
        return $l;
    }

    static function filter_wp_nav_menu_objects($items, $nav_menu) {
        $settings = admenu::get_settings();

        if (!in_array($settings->menu_type, ['location', 'menu',])) {
            return $items;
        }

        if ($settings->menu_type === 'location' && $nav_menu->theme_location !== $settings->menu_key) {
            return $items;
        }

        if ($settings->menu_type === 'menu') {
            $locations = get_nav_menu_locations();
            $menu_object = get_term($locations[$nav_menu->theme_location], 'nav_menu');
            if (intval($menu_object->term_id) !== intval($settings->menu_key)) {
                return $items;
            }
        }

        if (is_user_logged_in()) {
            // add /wp-admin link to logged in admin users
            if (current_user_can('manage_options') && $settings->show_wp_admin) {
                $items[] = self::get_menu_item_wp_admin();
            }

            // add logout button to anyone who is logged in
            $items[] = self::get_menu_item_logout();
        } else {
            // add outside menu items
            $login_item = self::get_menu_item_login();
            if (!is_null($login_item)) {
                $items[] = $login_item;
                if (!is_null($recover_item = self::get_menu_item_recover($login_item->db_id))) {
                    $items[] = $recover_item;
                }
            }
            if (!is_null($register_item = self::get_menu_item_register()) && settings::users_can_register()) {
                $items[] = $register_item;
            }
        }

        return $items;
    }

    static function initialize() {
        if (admenu::get_settings()->enabled) {
            add_filter('wp_nav_menu_objects', [__CLASS__, 'filter_wp_nav_menu_objects',], 10, 2);
        }
    }

}
