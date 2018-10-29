<?php
    global $wqkm_template_data;
    extract($wqkm_template_data); 
?>
<form id="wqkm_slider_frm" action="" method="POST" >
    <div id="wqkm-slider-msg"></div>
    <table class="form-table">
        <tr>
            <th><label><?php _e('Product Slider Images','wqkm'); ?>*</label></th>
            <td>
                <div id="wqkm-slider-images-panel"><?php echo $display_images; ?></div>
                <input type="file" name="wqkm_slider_image" id="wqkm_slider_image" value="" />
            </td>
        </tr>        
        <tr>
            <th>&nbsp;</th>
            <td><input name="wqkm_ui_submit" id="wqkm_ui_submit" type="button" value="<?php _e('Add Image','wqkm'); ?>" /></td>
        </tr>
    </table>
</form>

