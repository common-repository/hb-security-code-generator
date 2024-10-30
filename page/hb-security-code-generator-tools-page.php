<?php
if (!defined('ABSPATH') || !current_user_can('administrator')) {
    http_response_code(404);
    die();
}

$Settingurl   = wp_nonce_url('?page=HBSecurityCode&Setting=true','HB-Security-Code-Generator-setting');
?>

<span class="HBSecurity-Code-List" style="margin-right: -5px;"><a href="?page=HBSecurityCode">List</a></span>
<span class="HBSecurity-Code-Generate  HBSecurity-Code-List-Active" style="margin-right: -5px;"><a href="javascript:void(0)">Generate</a></span>

<?php if(false):?>
    <div class="HB-alert HB-alert-success">
        <?php
            echo esc_html(__( 'Successfully Added','hb-security-code-generator' ));
            echo ' ';
            echo esc_attr($hb_success);
        ?>
    </div>
<?php endif;?>
<form action="" method="post" id="VerificationCode">
    <h2><?php esc_html_e( 'Verification Code Generation','hb-security-code-generator' ); ?></h2>
    <label for=""><?php esc_html_e( 'Verification Code Length:','hb-security-code-generator' ); ?></label>
    <br>
    <input type="number" name="hb_length" min="2" max="50" value="15">
    <br><br>
    <label for=""><?php esc_html_e( 'Verification Code Prefix:','hb-security-code-generator' ); ?></label>
    <br>
    <input type="text" name="hb_prefix">
    <br><br>
    <label for=""><?php esc_html_e( 'Verification Code Rule:','hb-security-code-generator' ); ?></label>
    <br>
    <select name="hb_rule">
        <option value="2"><?php esc_html_e( 'Number + English','hb-security-code-generator' ); ?></option>
        <option value="1"><?php esc_html_e( 'Number Only','hb-security-code-generator' ); ?></option>
        <option value="3"><?php esc_html_e( 'Number + English(lowercase)','hb-security-code-generator' ); ?></option>
    </select>
    <br><br>
    <input type="checkbox" value="true" name="hb_no">
    <label for=""><?php esc_html_e( 'NO  “O”','hb-security-code-generator' ); ?></label>
    <br><br>
    <label for=""><?php esc_html_e( 'Quantity:','hb-security-code-generator' ); ?></label>
    <br>
    <input type="number" name="hb_quantity" min="1" max="100" value="10">
    <br><br>
    <label for=""><?php esc_html_e( 'Product Name:','hb-security-code-generator' ); ?></label>
    <br>
    <input type="text" name="hb_name">
    <br><br>
    <label for=""><?php esc_html_e( 'Enable:','hb-security-code-generator' ); ?></label>
    <br>
    <select name="hb_enable">
        <option value="enable"/><?php esc_html_e( 'Enable','hb-security-code-generator' ); ?></option>
        <option value="disenable"><?php esc_html_e( 'Disabled','hb-security-code-generator' ); ?></option>
    </select>
    <br><br>
    <label for=""><?php esc_html_e( 'When after the first query, destroy it after a few days (close the query)','hb-security-code-generator' ); ?></label>
    <br>
    <input type="number" name="hb_deadline" min="-1">
    <p><?php esc_html_e( 'If the value is “-1”, it will be invalid immediately after the first query','hb-security-code-generator' ); ?></p>
    <p><?php esc_html_e( 'Does not invalidate if value is “0” or empty','hb-security-code-generator' ); ?></p>
    <br><br>
    <input type="submit" class="button button-primary"  id="hbMake" value="<?php esc_html_e( 'Make','hb-security-code-generator' ); ?>">
</form>