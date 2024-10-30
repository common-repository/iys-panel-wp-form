<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'im4wp_form_data', 'im4wp_add_name_data', 60 );
add_filter( 'im4wp_integration_data', 'im4wp_add_name_data', 60 );

add_filter( 'mctb_data', 'im4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'im4wp_form_data', 'im4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'im4wp_integration_data', 'im4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'iyspanel_sync_user_data', 'im4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'im4wp_use_sslverify', 'im4wp_use_sslverify', 1 );
