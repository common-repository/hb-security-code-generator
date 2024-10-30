<?php
/*
 * Plugin Name: HB Security Code Generator
 * Plugin URI: https://piglet.me/SecurityCode
 * Description: A Security Code Generator
 * Version: 0.1.1
 * Author: heiblack
 * Author URI: https://piglet.me
 * License:  GPL 3.0
 * Domain Path: /languages
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/


class hb_security_code_generator_security_code
{
    public function __construct()
    {
        if (!defined('ABSPATH')) {
            http_response_code(404);
            die();
        }
        if (!function_exists('plugin_dir_url')) {
            return;
        }
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            return;
        }
        $this->init();
    }

    public  function init()
    {
        global $wpdb;

        define("HBSECURITYCODEGENERATOR","HBSecurityCode");


        $this->HBinItializationSecurityCode();

        $table_name = "wp_heiblack_wc_securitycode";
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) == $table_name ) {
            //Add 'Setting' link  Plugin
            $this->HBAddpluginlink();
            //Add Page In Account
            $this->HBSecurityCodeGeneratorEnable();
            //Add List Table
            $this->HBSecurityCodeGeneratorSave();

            //Ajax function
            $this->HBSecurityCodeGeneratorClose();
            $this->HBAddsecuritycodeTab();
            $this->HBSecurityCodeGeneratorMake();
        }
    }

    private function HBAddpluginlink(){
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), function ( $links ) {
            $links[] = '<a href="' .
                admin_url( 'tools.php?page=HBSecurityCode' ) .
                '">' . esc_html(__('Settings')) . '</a>';
            return $links;
        });


    }


    private  function HBinItializationSecurityCode(){
        register_activation_hook( __FILE__, function (){
            global $wpdb;
            $table_name = "wp_heiblack_wc_securitycode";
                if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) != $table_name ) {
                    $charset_collate = $wpdb->get_charset_collate();
                    try {
                        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                                    hb_id bigint(20) NOT NULL AUTO_INCREMENT,
                                    hb_code varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                                    hb_name text COLLATE utf8_unicode_ci NOT NULL,
                                    hb_status tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
                                    hb_deadline datetime DEFAULT NULL,
                                    hb_times int(11) NOT NULL DEFAULT 0,
                                    hb_ip longtext COLLATE utf8_unicode_ci NOT NULL,
                                    hb_data longtext COLLATE utf8_unicode_ci NOT NULL,
                                    hb_date timestamp NOT NULL DEFAULT current_timestamp(),
                                    PRIMARY KEY (hb_id) ,
                                    UNIQUE KEY hb_code (hb_code) USING HASH
                                ) $charset_collate";
                        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                        dbDelta($sql);

                    } catch (Exception $e) {
                        return fasle;
                    }
                }

        });

    }
    //Add Page In Account
    private  function HBAddsecuritycodeTab(){
        add_action( 'init', function (){
            add_rewrite_endpoint( 'hb-verification-code', EP_ROOT | EP_PAGES );
        } );
        add_filter( 'query_vars', function($vars){
            $vars[] = 'hb-verification-code';
            return $vars;
        }, 0 );
        add_filter( 'woocommerce_account_menu_items', function ($items){
            $items['hb-verification-code'] = __('Verification Code','hb-security-code-generator');
            return $items;
        });
        add_action( 'woocommerce_account_hb-verification-code_endpoint', function (){

            require_once dirname(__FILE__) . '/page/hb-hb-verification-code-user.php';
            wp_enqueue_script('HEIBLACK-SecurityCodeGenerator-code-js', plugin_dir_url(__FILE__) . 'assets/hb-securitycodeGenerator.code.js');
            wp_enqueue_style('HEIBLACK-security-code-user-css', plugin_dir_url(__FILE__) . 'assets/HBSecurityCode.user.css');
        });

    }
    //Add List Table
    private  function HBSecurityCodeGeneratorEnable(){
        require_once dirname(__FILE__) . '/page/hb-security-code-generator-list-table.php';
        if(isset($_GET['page']) && ($_GET['page']==HBSECURITYCODEGENERATOR)){
           wp_enqueue_style('HEIBLACK-security-code-css', plugin_dir_url(__FILE__) . 'assets/style.css');
           wp_enqueue_script('HEIBLACK-SecurityCode-js', plugin_dir_url(__FILE__) . 'assets/hb-securitycodeGenerator.admin.js');
        }
    }


    //Ajax function

    //Message Ajax Save
    private  function HBSecurityCodeGeneratorSave(){
        add_action('wp_ajax_hb_save_securitycodeGenerator_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                global $wpdb;
                if ( isset( $_POST['HBsecurityID'], $_POST['wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wpnonce'] ), 'HB-Security-Code-Generator' ) ) {
                    $hbRequestId    = sanitize_text_field($_POST['HBsecurityID']);
                    $HBenable       = sanitize_text_field($_POST['HBenable']);
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE wp_heiblack_wc_securitycode SET hb_status = %d WHERE hb_id = %d;",
                            sanitize_text_field($HBenable),sanitize_text_field($hbRequestId)
                        )
                    );
                    die('0');
                }
                die('1');
            }
            die('2');
        });
    }
    //Close Ajax Save
    private  function HBSecurityCodeGeneratorClose(){
        add_action('wp_ajax_hb_close_securitycodeGenerator_action', function (){
            if ( current_user_can( 'administrator' ) ) {
                global $wpdb;
                if ( isset( $_POST['HBCloseID'], $_POST['wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wpnonce'] ), 'HB-Security-Code-Generator-close' ) ) {
                    $HBCloseID    = sanitize_text_field($_POST['HBCloseID']);
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE wp_heiblack_wc_securitycode SET hb_status = %d WHERE hb_id = %d;",
                            2,sanitize_text_field($HBCloseID)
                        )
                    );
                    die('0');
                }
                die('1');
            }
            die('2');
        });
    }
    //Verification Code Generation
    private  function HBSecurityCodeGeneratorMake(){
        add_action('wp_ajax_export_client_price_csv', function (){
            if ( current_user_can( 'administrator' ) ) {
                $hb_success = null;
                if(@$_POST){
                    require_once dirname(__FILE__) . '/page/export_csv_class.php';
                    $export_code = new hb_security_code_generator_security_export_csv_class;
                    if (class_exists('hb_security_code_generator_security_export_csv_class') || !defined('ABSPATH') || current_user_can('administrator')) {
                        $hb_length      = sanitize_text_field($_POST['hb_length']);
                        $hb_quantity    = sanitize_text_field($_POST['hb_quantity']);
                        $hb_prefix      = sanitize_text_field($_POST['hb_prefix']);
                        $hb_rule        = sanitize_text_field($_POST['hb_rule']);
                        $hb_name        = sanitize_text_field($_POST['hb_name']);
                        $hb_no          = isset($_POST['hb_no']) ? sanitize_text_field($_POST['hb_no']) : '';
                        $hb_deadline    = sanitize_text_field($_POST['hb_deadline']);
                        $hb_enable      = sanitize_text_field($_POST['hb_enable']);
                        if($hb_enable=='enable'){
                            $hb_enable='1';
                        }else{
                            $hb_enable='0';
                        }
                        $hb_deadline_array = [];
                        if($hb_deadline){
                            if(is_numeric($hb_deadline)){
                                $hb_deadline_array[] = array('hb_deadline'=>$hb_deadline);
                            }
                        }
                        $hb_deadline = serialize($hb_deadline_array);
                        $hb_success =0;
                        $code_data = [];
                        for ($i=0; $i < $hb_quantity ; $i++) {
                            global $wpdb;
                            $code = $export_code->generator_code($hb_length,$hb_no,$hb_rule);
                            $array2  = $hb_prefix.'-'.$code;
                            $result = $wpdb->insert('wp_heiblack_wc_securitycode',
                                array(
                                    'hb_code'       =>sanitize_text_field($array2),
                                    'hb_status'     =>sanitize_text_field($hb_enable),
                                    'hb_name'       =>sanitize_text_field($hb_name),
                                    'hb_data'       =>sanitize_text_field($hb_deadline),
                                ),
                                array('%s','%d','%s','%s'));
                            if($result){
                                $code_data[] = array($hb_name,$array2);
                                $hb_success +=1;
                            }
                        }
                        $export_code->export_csv_init($code_data,$hb_name);
                        die();
                    }else{
                        http_response_code(404);
                        die();
                    }
                }
            }
            die('900');
        });
    }


}

new  hb_security_code_generator_security_code();
