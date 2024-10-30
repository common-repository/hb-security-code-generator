<?php
    if(@$_POST['SecurityCode']){
        global  $wpdb;
        $SecurityCode = sanitize_text_field($_POST['SecurityCode']);

        //Simple Get IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        $times = 1;

        $now = current_time( 'mysql' );

        $result             = $wpdb->get_results($wpdb->prepare("
                                            SELECT
                                               `hb_id`,
                                               `hb_name`,
                                               `hb_ip`,
                                               `hb_deadline`,
                                               `hb_data`,
                                               `hb_times`
                                            FROM `wp_heiblack_wc_securitycode`  WHERE hb_code = %s AND hb_status = %d ",esc_html($SecurityCode),1));



        if(count($result)<=0 || ($result[0]->hb_deadline != NULL && strtotime($result[0]->hb_deadline) > 0 && $result[0]->hb_deadline < $now)    ) {
            $noinformation = '<div class="HB-alert HB-alert-error">'.__('No Information', 'hb-security-code-generator').'</div>';
            echo wp_kses_post($noinformation);
            return;
        }

        $hb_id = $result[0]->hb_id;


        if( $result[0]->hb_deadline == NULL && $result[0]->hb_data){
           $hb_data  = unserialize($result[0]->hb_data);
           if(isset($hb_data[0]['hb_deadline'])){
               $hb_data =  $hb_data[0]['hb_deadline'];
               $now = current_time( 'mysql' );
               $hb_deadline_verify = date( 'Y-m-d H:i:s', strtotime( $now ) + 60*60*24*intval($hb_data) );



               $wpdb->query(
                   $wpdb->prepare(
                       "UPDATE wp_heiblack_wc_securitycode SET hb_deadline = %s WHERE hb_id = %d;",
                       sanitize_text_field($hb_deadline_verify),sanitize_text_field($hb_id)
                   )
               );


           }
        }


        $iparray = unserialize($result[0]->hb_ip);
        $hasvalue = false;

        if(!$iparray){
            $iparray = [];
        }


        foreach ($iparray as $key=>$value){

            if($value[0]==$ip){
                $iparray[$key][1]+=1;
                $hasvalue = true;
                $iparray = serialize($iparray);
                break;
            }


        }

        if($hasvalue == false){
            $iparray[] = array($ip,$times,$now);
            $iparray = serialize($iparray);
        }


        if($hasvalue == true){
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE wp_heiblack_wc_securitycode SET hb_ip = %s WHERE hb_id = %d;",
                    sanitize_text_field($iparray),sanitize_text_field($hb_id)
                )
            );
        }else{
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE wp_heiblack_wc_securitycode SET hb_times = hb_times + 1 ,hb_ip = %s WHERE hb_id = %d;",
                    sanitize_text_field($iparray),sanitize_text_field($hb_id)
                )
            );
        }
        $result = $result[0];

    }

?>

<?php if(!@$_POST['SecurityCode']):?>
<?php echo '<h3>'.esc_html(__('Verification code', 'hb-security-code-generator')).'</h3>';?>
<form  id="myform" method="post">
    <input type="text" id="SecurityCode" name="SecurityCode" autocomplete="off">
    <input type="text" id="hbcode" name="hbcode" autocomplete="off">
    <br>
    <canvas id="mycanvas" width='150' height='40' ></canvas>
    <br>
    <?php wp_nonce_field( '_hb-security-code-generator');?>
    <input type="submit" >
</form>
<?php elseif(@$_POST['SecurityCode'] && wp_verify_nonce( $_POST['_wpnonce'], '_hb-security-code-generator')): ?>
    <h2><?php esc_html_e('Search Result', 'hb-security-code-generator'); ?></h2>
    <div style="text-align: center">
        <svg xmlns="http://www.w3.org/2000/svg" height="100px" viewBox="0 0 24 24" width="100px" fill="#28B463">
            <path d="M0 0h24v24H0z" fill="none"/><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
        </svg>
        <h3><?php esc_html_e('Genuine Product', 'hb-security-code-generator'); ?></h3>
    </div>
    <div class="HBbox">
        <?php echo esc_textarea($_POST['SecurityCode']);?>
    </div>
    <div class="HBflex">
        <div class="HBitem HBName">
            <div>
                <h3><?php esc_html_e('Product Name', 'hb-security-code-generator'); ?></h3>
                <span><?php echo esc_textarea($result->hb_name);?></span>
            </div>
        </div>
        <div class="HBitem HBtimes">
            <div>
                <h3><?php esc_html_e('Query Times', 'hb-security-code-generator'); ?></h3>
                <span><?php echo esc_textarea($result->hb_times)+1?></span>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php esc_html_e('Error', 'hb-security-code-generator'); ?>
<?php endif?>

