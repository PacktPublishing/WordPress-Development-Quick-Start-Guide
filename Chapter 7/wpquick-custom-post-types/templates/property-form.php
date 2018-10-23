<?php
    global $template_data;
    extract($template_data); 
?>
<form action="" method="POST" >
    <input type="hidden" name="property_nonce" value="<?php echo $property_nonce; ?>" />

    <table class="form-table">
        <tr>
            <th><label><?php _e('Property Title','wqcpt'); ?>*</label></th>
            <td><input type="text" name="wqcpt_prfr_title" id="wqcpt_prfr_title" value="" /></td>
        </tr>
        <tr>
            <th><label><?php _e('Property Content','wqcpt'); ?>*</label></th>
            <td><textarea name="wqcpt_prfr_content" id="wqcpt_prfr_content" ></textarea></td>
        </tr>    
        <tr>
            <th><label><?php _e('Type','wqcpt'); ?>*</label></th>
            <td><select class="widefat" name="wqcpt_prfr_type" id="wqcpt_prfr_type">
                <option value="0" ><?php _e('Please Select','wqcpt'); ?></option>
                <option value="house" ><?php _e('House','wqcpt'); ?></option> 
                <option value="office" ><?php _e('Office','wqcpt'); ?></option>               
            </select></td>
        </tr>    
        <tr>
            <th><label><?php _e('City','wqcpt'); ?></label></th>
            <td><input class="widefat" name="wqcpt_prfr_city" id="wqcpt_prfr_city" type="text" value="<?php echo $wqcpt_pr_city; ?>" /></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td><input name="wqcpt_prfr_submit" id="wqcpt_prfr_submit" type="submit" value="<?php _e('Add Property','wqcpt'); ?>" /></td>
        </tr>
    </table>
</form>

