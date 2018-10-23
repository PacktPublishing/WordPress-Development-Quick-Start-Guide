<?php
    global $template_data;
    extract($template_data);    
?>

<input type="hidden" name="property_nonce" value="<?php echo $property_nonce; ?>" />

<table class="form-table">    
    <tr>
        <th><label><?php _e('Type','wqcpt'); ?>*</label></th>
        <td><select class="widefat" name="wqcpt_pr_type" id="wqcpt_pr_type">
            <option <?php selected( $wqcpt_pr_type, "0" ); ?> value="0" ><?php _e('Please Select','wqcpt'); ?></option>
            <option <?php selected( $wqcpt_pr_type, "house" ); ?> value="house" ><?php _e('House','wqcpt'); ?></option> 
            <option <?php selected( $wqcpt_pr_type, "office" ); ?> value="office" ><?php _e('Office','wqcpt'); ?></option> 
                
        </select></td>
    </tr>
    <tr>
        <th><label><?php _e('City','wqcpt'); ?></label></th>
        <td><input class="widefat" name="wqcpt_pr_city" id="wqcpt_pr_city" type="text" value="<?php echo $wqcpt_pr_city; ?>" /></td>
    </tr>  
    
</table>
