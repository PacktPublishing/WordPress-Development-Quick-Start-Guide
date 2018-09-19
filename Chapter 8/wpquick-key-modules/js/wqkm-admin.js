jQuery(document).ready( function($) {
	$('#wqkm_ui_submit').click(function(e){

      e.preventDefault();
      var file_form = $('#wqkm_slider_frm');    
      var file = file_form.find('#wqkm_slider_image').val();  

      var msg_container = file_form.find('#wqkm-slider-msg');
      msg_container.removeClass('wqkm-message-info-error').removeClass('wqkm-message-info-success');

      var err = 0;
      var err_msg = '';      

      if(file == '' ){
        err_msg += '' + WQKMAdmin.Messages.fileRequired + '<br/>';
        err++;
      }

      if(err != 0){
        msg_container.html(err_msg).addClass('wqkm-message-info-error').show();
      }else{

            msg_container.html('').hide();

            var formObj = file_form;
            var formURL = WQKMAdmin.AdminAjax+'?action=wqkm_save_slider_images';
            var formData = new FormData();
            var file_data = $('#wqkm_slider_image').prop('files')[0];   
            formData.append('file_nonce', WQKMAdmin.nonce);
            formData.append('file_data', file_data);
            jQuery.ajax({
                url: formURL,
                type: 'POST',
                data:  formData,
                mimeType:'multipart/form-data',
                contentType: false,
                cache: false,
                dataType : 'json',
                processData:false,
                success: function(data, textStatus, jqXHR)
                {
                  if(data.status == 'success'){
                    msg_container.html(data.msg).removeClass('wqkm-message-info-error').addClass('wqkm-message-info-success').show();
                    $('#wqkm-slider-images-panel').html(data.images);
                  
                    file_form.find('#wqkm_slider_image').val('');
                  
                  }else if(data.status == 'error'){
                    msg_container.html(data.msg).removeClass('wqkm-message-info-success').addClass('wqkm-message-info-error').show();
                  }
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    msg_container.html(err_msg).addClass('wqkm-message-info-error').show();
                }
            });
      }
    });

  
});