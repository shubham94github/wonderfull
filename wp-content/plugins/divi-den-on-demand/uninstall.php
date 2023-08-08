<?php
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    global $wpdb;
    $table_name =  $wpdb->prefix.'options';
    $wpdb->query( 'DELETE FROM ' . $table_name. ' WHERE option_name LIKE "%disable-ddd%"' );
}