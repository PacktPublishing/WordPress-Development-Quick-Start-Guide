jQuery(document).ready( function($) {
	$('#wqwpf_product_file_upload').click(function(e){

      e.preventDefault();
      var file_form = $('#post');    
      var file = file_form.find('#wqwpf_product_files').val();  
      var post_id = $('#post_ID').val();

      var msg_container = file_form.find('#wqwpf-product-files-msg');
      msg_container.removeClass('wqwpf-message-info-error').removeClass('wqwpf-message-info-success');

      var err = 0;
      var err_msg = '';      

      if(file == '' ){
        err_msg += '' + WQWPFAdmin.Messages.fileRequired + '<br/>';
        err++;
      }

      if(err != 0){
        msg_container.html(err_msg).addClass('wqwpf-message-info-error').show();
      }else{

            msg_container.html('').hide();

            var formObj = file_form;
            var formURL = WQWPFAdmin.AdminAjax+'?action=wqwpf_save_product_files';
            var formData = new FormData();
            var file_data = $('#wqwpf_product_files').prop('files')[0];   
            formData.append('post_id', post_id);            
            formData.append('file_nonce', WQWPFAdmin.nonce);
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
                    msg_container.html(data.msg).removeClass('wqwpf-message-info-error').addClass('wqwpf-message-info-success').show();
                    $('#wqwpf-files-container').html(data.files);
                  
                    file_form.find('#wqwpf_product_files').val('');
                  
                  }else if(data.status == 'error'){
                    msg_container.html(data.msg).removeClass('wqwpf-message-info-success').addClass('wqwpf-message-info-error').show();
                  }
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    msg_container.html(err_msg).addClass('wqwpf-message-info-error').show();
                }
            });
      }
    });
});