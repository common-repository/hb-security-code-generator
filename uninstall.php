<?php
if( ! defined ('WP_UNINSTALL_PLUGIN') )
    exit();
function wc_hb_securitycode_delete_plugin(){
    global $wpdb;

    $table_name = "wp_heiblack_wc_securitycode";
    //delete option settings
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

}

wc_hb_securitycode_delete_plugin();