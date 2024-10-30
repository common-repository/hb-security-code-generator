<?php
if (!defined('ABSPATH') || !current_user_can('administrator')) {
    http_response_code(404);
    die();
}

global  $wpdb;
$hbRequestId      = sanitize_text_field($_GET['HBsecurityID']);
$result           = $wpdb->get_results($wpdb->prepare("
                                            SELECT
                                               *
                                            FROM `wp_heiblack_wc_securitycode`  WHERE hb_id =%d",esc_html($hbRequestId)));

if (!$result) return;

$result         =  $result[0];
$hb_id          =  $result->hb_id;
$HBsecurityID   = sanitize_text_field($_GET['HBsecurityID']);
$wpnonce        = sanitize_text_field($_GET['_wpnonce']);
$now            = current_time( 'mysql' );

$hb_deadline = $result->hb_deadline;


$hb_ip = unserialize($result->hb_ip);

if(!$hb_ip){
    $hb_ip = [];
}


?>
<div id="HB-product">
    <table>
        <tr>
            <th><?php esc_html_e( 'Name','hb-security-code-generator' ); ?></th>
            <th><?php esc_html_e( 'Status','hb-security-code-generator' );?></th>
            <th><?php esc_html_e( 'Times(Different IP)','hb-security-code-generator' );?></th>
        </tr>
        <tr>
            <td><?php echo esc_textarea($result->hb_name); ?></td>
            <td>
                <?php
                    if($hb_deadline != NULL && strtotime($hb_deadline) > 0 && $hb_deadline < $now ) {
                        esc_html_e( 'Timeouts','hb-security-code-generator' );
                    }else{
                        if($result->hb_status==1){
                            esc_html_e( 'Enable','hb-security-code-generator' );
                        }else{
                            esc_html_e( 'Disenable','hb-security-code-generator' );
                        }
                    }
                ?>
            </td>
            <td><?php echo esc_textarea($result->hb_times); ?></td>
        </tr>
    </table>
</div>
<h2><?php esc_html_e( 'Code:', 'hb-security-code-generator' ); ?></h2>
<textarea disabled name="" id="HBsecurity" ><?php echo esc_textarea($result->hb_code); ?></textarea>
<h2><?php esc_html_e( 'IP:', 'hb-security-code-generator' ); ?></h2>
<textarea disabled name="" id="HBsecurity" ><?php foreach ($hb_ip as $key=>$value){
        if(isset($value[0])) echo  esc_textarea($value[0]);
        echo  '=>[';
        if(isset($value[1])) echo  esc_textarea($value[1]);
        echo  ' times ] [';
        if(isset($value[2])) echo  esc_textarea($value[2]);
        echo  ']';
        echo  "\n";
    }
     ?>
</textarea>
<?php if($hb_deadline == NULL || (strtotime($hb_deadline) > 0 && $hb_deadline > $now) ):?>
<h2><?php esc_html_e( 'Status:', 'hb-security-code-generator' ); ?></h2>
<form action=""method="post" id="HBSAVE">
    <input type="hidden" id="HBsecurityID" value="<?php echo  esc_textarea($HBsecurityID); ?>">
    <input type="hidden" id="wpnonce" value="<?php echo  esc_textarea($wpnonce); ?>">
    <select name="HBstatus" id="HBenable">
        <?php
        if($result->hb_status==0):?>
            <option value="0" selected="selected"><?php esc_html_e( 'disenable', 'hb-security-code-generator' ); ?></option>
            <option value="1"><?php esc_html_e( 'enable', 'hb-security-code-generator' ); ?></option>
        <?php elseif($result->hb_status==1):?>
            <option value="0"><?php esc_html_e( 'disenable', 'hb-security-code-generator' ); ?></option>
            <option value="1" selected="selected"><?php esc_html_e( 'enable', 'hb-security-code-generator' ); ?></option>
        <?php else:?>
            <option value="0"><?php esc_html_e( 'disenable', 'hb-security-code-generator' ); ?></option>
            <option value="1"><?php esc_html_e( 'enable', 'hb-security-code-generator' ); ?></option>

        <?php endif;?>
    </select>
    <br><br>
    <input type="submit" class="button button-primary" id="hb-security-save" value="Save">
</form>
<?php endif;?>

