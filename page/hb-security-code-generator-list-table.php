<?php

class HB_Wp_SC_List_Tables
{
    public function __construct()
    {
        if( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        $this->init();
    }
    public function init()
    {
        $this->HBAddAdminMenuInWoocommerce();
    }

    private function HBAddAdminMenuInWoocommerce(){
        add_action( 'admin_menu', function(){
            add_submenu_page(
                'tools.php',
                __('HB Security Code', 'hb-security-code-generator'),
                __('HB Security Code', 'hb-security-code-generator'),
                'administrator',
                'HBSecurityCode',
                function (){
                    $exampleListTable = new hb_security_code_generator_list_tables();

                    $exampleListTable->prepare_items();

                    if(empty($_GET[HBRequestId]) && !isset($_GET['Generate'])  && !isset($_GET['_wpnonce']) ) {

                        $Generateurl = wp_nonce_url('?page=HBSecurityCode&Generate=true', 'HB-Security-Code-Generator-make');
                        $Settingurl = wp_nonce_url('?page=HBSecurityCode&Setting=true', 'HB-Security-Code-Generator-setting');
                        $tabs = '<span class="HBSecurity-Code-List HBSecurity-Code-List-Active"><a href="javascript:void(0)">List</a></span>';
                        $tabs .= '<span class="HBSecurity-Code-Generate"><a href="' . esc_url($Generateurl) . '">Generate</a></span>';
                        echo wp_kses_post($tabs);
                        echo '<form method="post">';
                        echo '<div class="wrap">';
                        echo '<h2>';
                        esc_html_e('Request', 'hb-security-code-generator');
                        echo '</h2>';
                        $exampleListTable->search_box('search', 'search_id');
                        $exampleListTable->display();
                        echo '</div>';
                        echo '</form>';
                    }elseif (is_admin() && wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'HB-Security-Code-Generator' )){
                        require_once dirname(__FILE__) . '/hb-security-code-generator-message-page.php';
                    }elseif (is_admin()&& isset($_GET['Generate']) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'HB-Security-Code-Generator-make' )){
                        require_once dirname(__FILE__) . '/hb-security-code-generator-tools-page.php';
                    }elseif (is_admin()&& isset($_GET['Setting']) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'HB-Security-Code-Generator-setting' )){
                        require_once dirname(__FILE__) . '/hb-security-code-generator-setting-page.php';
                    }
                }
            );
        } );

    }

}

new HB_Wp_SC_List_Tables();



class hb_security_code_generator_list_tables extends WP_List_Table
{

    public function prepare_items()
    {

        if (!defined('ABSPATH') || !current_user_can('administrator')) {
            http_response_code(404);
            die();
        }


        $per_page               = 30;
        $columns                = $this->get_columns();
        $hidden                 = array();

        $this->_column_headers  = array($columns, $hidden);

        $currentPage            = $this->get_pagenum();
        $total_items            = $this->get_total_count();

        $offset                 = $per_page>0 ? (($currentPage-1)*$per_page) : 0;

        $data    = $this->get_data($per_page,$offset);


        $this->securityCode_action();

        $this->items = $data;
        $this->set_pagination_args(
            array(
                'total_items'   =>  $total_items,
                'per_page'      =>  $per_page,
                'total_pages'   =>  ceil($total_items/$per_page)
            )
        );
    }

    function get_data($per_page,$offset=0,$search=''){


        global $wpdb;
        $data = array();

        $search =  '%%';
        if (isset($_GET['page']) && isset($_POST['s'])) {
            $search =  sanitize_text_field($_POST['s']);
        }

        $result = $wpdb->get_results($wpdb->prepare("
                                            SELECT
                                                `hb_id`,
                                                `hb_code`,
                                                `hb_name`,
                                                `hb_times`,
                                                `hb_deadline`,
                                                `hb_status`
                                            FROM `wp_heiblack_wc_securitycode` WHERE `hb_code` LIKE %s ORDER BY `wp_heiblack_wc_securitycode`.`hb_id` DESC LIMIT %d,%d",sanitize_text_field($search),$offset,$per_page));

        $now = current_time( 'mysql' );

        foreach ($result as $value){

            $hb_id          = $value->hb_id;
            $hb_code        = $value->hb_code;
            $hb_name        = $value->hb_name;
            $hb_status      = $value->hb_status;
            $hb_times       = $value->hb_times;
            $hb_deadline    = $value->hb_deadline;

            $hb_name     = mb_substr( $hb_name, 0, 5, "UTF-8");
            $hb_code     = mb_substr( $hb_code, 0, 50, "UTF-8");

            $HBPRC          = '<input type="checkbox" name="hbsecurity[]" value="'.esc_textarea($hb_id).'">';


            $status ='';
            if( $hb_deadline < $now && (strtotime($hb_deadline) > 0 && $hb_deadline < $now)){
                    $timeout = __('Timeout','hb-security-code-generator');
                    $status .= ' <mark class="hb-order-status HBClosed" ><span>'.esc_html($timeout).'</span></mark>';
                    $HBPRC          = '<input type="checkbox" name="hbTimeout[]" value="'.esc_textarea($hb_id).'">';
            }elseif ($hb_status==2 ){
                $hb_status_reply = __('Closed','hb-security-code-generator');
                $status         = '<mark class="hb-order-status HBClosed" ><span>'.esc_html($hb_status_reply).'</span></mark>';
            }elseif ($hb_status==1){
                $hb_status_reply = __('Enable','hb-security-code-generator');
                $status         = '<mark class="hb-order-status HBEnable"><span>'.esc_html($hb_status_reply).'</span></mark>';
            }else{
                $hb_status_reply = __('Disabled','hb-security-code-generator');
                $status         = '<mark class="hb-order-status HBDisabled"><span>'.esc_html($hb_status_reply).'</span></mark>';
            }

            $url            = wp_nonce_url('?page=HBSecurityCode&HBsecurityID='.esc_textarea($hb_id),'HB-Security-Code-Generator');
            $actions        = "<a class=\"button\" href=".esc_url($url).">".esc_html('View','hb-security-code-generator')."</a> ";
           // $url2           = wp_nonce_url('?page=HBSecurityCode&HBCloseID='.esc_textarea($hb_id),'HB-Security-Code-Generator-close');
            $data[]         = array(
                'HB-Security-Code'              => $HBPRC,
                'HB-Security-Code-name'         => esc_textarea($hb_name),
                'HB-Security-Code-code'         => esc_textarea($hb_code),
                'HB-Security-Code-status'       => wp_kses_post($status),
                'HB-Security-Code-times'        => esc_textarea($hb_times),
                'HB-Security-Code-actions'      => wp_kses_post($actions)
            );
        }
        return $data;
    }
    function get_bulk_actions(){
        $actions = array(
            'enable'        => __('Enable','hb-security-code-generator'),
            'delete'        => __('Delete','hb-security-code-generator'),
            'disabled'      => __('Disabled','hb-security-code-generator'),

        );
        return $actions;
    }
    function securityCode_action(){
        if('enable' === $this->current_action() && isset($_POST['action']) && $_POST['action']=='enable'){
            if(isset($_POST['hbTimeout']) ){
                if($hbsecurity_size = count($_POST['hbTimeout'])){
                    global $wpdb;
                    for($i = 0; $i < $hbsecurity_size; $i++){
                        $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE wp_heiblack_wc_securitycode SET hb_status= %d ,hb_deadline = NULL WHERE hb_id = %d;",
                                1,sanitize_text_field($_POST['hbTimeout'][$i])
                            )
                        );

                    }
                }
            }
            if(isset($_POST['hbsecurity']) ){
                if($hbsecurity_size = count($_POST['hbsecurity'])){
                    global $wpdb;
                    for($i = 0; $i < $hbsecurity_size; $i++){
                        $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE wp_heiblack_wc_securitycode SET hb_status= %d  WHERE hb_id = %d;",
                                1,sanitize_text_field($_POST['hbsecurity'][$i])
                            )
                        );

                    }
                }
            }
            echo "<script>location.reload();</script>";
        } elseif('delete' === $this->current_action() && isset($_POST['action']) && $_POST['action']=='delete'){
            if(isset($_POST['hbTimeout'])){
                $size = count($_POST['hbTimeout']);
                if($size){
                    global $wpdb;

                    for($i = 0; $i < $size; $i++){
                        $wpdb->query(
                            $wpdb->prepare(
                                "DELETE FROM wp_heiblack_wc_securitycode WHERE hb_id = %d;",
                                sanitize_text_field($_POST['hbTimeout'][$i])
                            )
                        );
                    }
                }
            }
            if(isset($_POST['hbsecurity'])){
                $size = count($_POST['hbsecurity']);
                if($size){
                    global $wpdb;

                    for($i = 0; $i < $size; $i++){
                        $wpdb->query(
                            $wpdb->prepare(
                                "DELETE FROM wp_heiblack_wc_securitycode WHERE hb_id = %d;",
                                sanitize_text_field($_POST['hbsecurity'][$i])
                            )
                        );


                    }

                }
            }

            echo "<script>location.reload();</script>";
        }elseif('disabled' === $this->current_action() && isset($_POST['action']) && $_POST['action']=='disabled'){
            if(isset($_POST['hbTimeout']) ){
                echo "<script>alert('Timeout is not allowed to be set to disabled');</script>";
            }
            if(isset($_POST['hbsecurity']) ){
                $hbsecurity_size = count($_POST['hbsecurity']);
                if($hbsecurity_size){
                    global $wpdb;
                    for($i = 0; $i < $hbsecurity_size; $i++){
                        $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE wp_heiblack_wc_securitycode SET hb_status= %d  WHERE hb_id = %d;",
                                0,sanitize_text_field($_POST['hbsecurity'][$i])
                            )
                        );

                    }
                }
            }
            echo "<script>location.reload();</script>";
        }
    }
    function get_total_count(){
        global $wpdb;
        $total_query = "SELECT COUNT(*) FROM wp_heiblack_wc_securitycode";
        $count = $wpdb->get_var( $total_query );
        return $count;
    }
    public function get_columns()
    {
        $columns = array(
            'HB-Security-Code'             =>'<input type="checkbox" class="HBSECALL">',
            'HB-Security-Code-name'        => __('name','hb-security-code-generator'),
            'HB-Security-Code-code'        => __('Code(50)','hb-security-code-generator'),
            'HB-Security-Code-status'      => __('Status','hb-security-code-generator'),
            'HB-Security-Code-times'       => __('times','hb-security-code-generator'),
            'HB-Security-Code-actions'     => __('Actions','hb-security-code-generator')
        );

        return $columns;
    }


    public function get_sortable_columns()
    {
        //return array('date' => array('date', false));
        return array();
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'HB-Security-Code':
            case 'HB-Security-Code-name':
            case 'HB-Security-Code-code':
            case 'HB-Security-Code-status':
            case 'HB-Security-Code-times':
            case 'HB-Security-Code-actions':
                return $item[ $column_name ];
            default:
                return  false;
        }
    }

    private function sort_data()
    {


    }
}




