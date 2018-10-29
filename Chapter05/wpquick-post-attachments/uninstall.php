<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
 
delete_option('wpqpa_version');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpqpa_post_attachments");