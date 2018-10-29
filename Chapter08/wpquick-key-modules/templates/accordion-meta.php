<?php
    global $template_data;
    extract($template_data);    
?>

<input type="hidden" name="accordion_nonce" value="<?php echo $accordion_nonce; ?>" />
<table class="form-table">    
    <tr>
        <th><label><?php _e( 'Tab 1','wqkm' ); ?></label></th>
        <td><?php wp_editor( $wqkm_tab_1, 'wqkm_tab_1' ); ?></td>
    </tr>
    <tr>
        <th><label><?php _e( 'Tab 2','wqkm' ); ?></label></th>
        <td><?php wp_editor( $wqkm_tab_2, 'wqkm_tab_2' ); ?></td>
    </tr>
    <tr>
        <th><label><?php _e( 'Tab 3','wqkm' ); ?></label></th>
        <td><?php wp_editor( $wqkm_tab_3, 'wqkm_tab_3' ); ?></td>
    </tr>   
</table>
