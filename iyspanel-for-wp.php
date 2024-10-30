<?php
/*
   Plugin Name: İYS Panel WP Form
   Plugin URI: https://iyspanel.com/iys-uyumlu-wordpress-form-eklentisi/
   Description: İYS compatible WordPress form plug-in You don’t have to deal with; learning the regulation, working on API structure and integration, development process and budget etc. İYS Panel WP Form enables you to design and add forms to your website through which you can gather your visitors’ data, get verification and automatically transfer the consent information to the IYS (Turkish Communication Management System).
   Version: 1.0.3
   Author: İletişim Makinesi
   Author URI: https://iletisimmakinesi.com/
   Text Domain: iys-panel-wp-form
   Domain Path: /languages
   License: GPL v3

   İYS Panel WP
   Copyright (C) 2012-2021, Hermes Iletisim, hizmet@hermesiletisim.net

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

/** @ignore */
function im4wp_load_plugin() {
    global $im4wp;

    // don't run if İYS Panel WP Form Pro 2.x is activated
    if ( defined( 'IM4WP_VERSION' ) ) {
        return;
    }

    // don't run if PHP version is lower than 5.3
    if ( ! function_exists( 'array_replace' ) ) {
        return;
    }

    // bootstrap the core plugin
    define( 'IM4WP_VERSION', '1.0.3' );
    define( 'IM4WP_PLUGIN_DIRDIR', __DIR__ );
    define( 'IM4WP_PLUGIN_DIRFILE', __FILE__ );

    // load autoloader if function not yet exists (for compat with sitewide autoloader)
    if ( ! function_exists( 'im4wp' ) ) {
        require_once IM4WP_PLUGIN_DIRDIR . '/vendor/autoload.php';
    }

    require IM4WP_PLUGIN_DIRDIR . '/includes/default-actions.php';
    require IM4WP_PLUGIN_DIRDIR . '/includes/default-filters.php';

    // require API class manually because Composer's classloader is case-sensitive
    // but we need it to pass class_exists condition
    require IM4WP_PLUGIN_DIRDIR . '/includes/api/class-api-v1.php';

    /**
     * @global IM4WP_Container $GLOBALS['im4wp']
     * @name $im4wp
     */
    $im4wp = im4wp();
    $im4wp['api'] = 'im4wp_get_api_v1';
    $im4wp['log'] = 'im4wp_get_debug_log';

    // forms
    $im4wp['forms'] = new IM4WP_Form_Manager();
    $im4wp['forms']->add_hooks();

    // Doing cron? Load Usage Tracking class.
    if ( isset( $_GET['doing_wp_cron'] ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
        IM4WP_Usage_Tracking::instance()->add_hooks();
    }

    // Initialize admin section of plugin
    if ( is_admin() ) {
        $admin_tools = new IM4WP_Admin_Tools();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $ajax = new IM4WP_Admin_Ajax( $admin_tools );
            $ajax->add_hooks();
        } else {
            $messages = new IM4WP_Admin_Messages();
            $im4wp['admin.messages'] = $messages;

            $admin = new IM4WP_Admin( $admin_tools, $messages );
            $admin->add_hooks();

            $forms_admin = new IM4WP_Forms_Admin( $messages );
            $forms_admin->add_hooks();

            $forms_list = new IM4WP_List_Forms( $messages );
            $forms_list->add_hooks();
        }
    }
}

add_action( 'plugins_loaded', 'im4wp_load_plugin', 8 );
