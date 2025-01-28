let xhr;

jQuery(document).ready(function($){
    /*jQuery(".menu-list li.current-page").removeClass("current-page");
    var currentPage = window.location.pathname.replaceAll("/","");
    jQuery(".menu-list li."+ currentPage ).addClass("current-page");
    */
    
    jQuery(".choose-all-rows").click(function(){
        if(jQuery(this).is(':checked')){
            jQuery('.checkbox-row').prop('checked', true);
        }
        else{
            jQuery('.checkbox-row').prop('checked', false);
        }
    })
    jQuery(".checkbox-row").click(function(){
        /*if(jQuery(this).is(':checked') && jQuery("#sub-table-container").is("div")){
           jQuery.ajax({
            dataType: 'json',
            method: 'POST',
            url: "/wp-admin/admin-ajax.php",
            data: 
            {   page_name:jQuery('input[name="page-name"]').val(),
                parent_id_value: parseInt( jQuery(this).val()),
                action: 'get_sub_table_checked_row'
            },
            success: function (output) {
                jQuery("#sub-table-container table").remove();
                jQuery("#sub-table-container").append(output);
            }
        }); 
        }*/
    })
    
    
    
    // logout
    jQuery('#logout').click(function(){
        jQuery.ajax({
            type: "GET",
            url: "/wp-admin/admin-ajax.php",
            data: { action: 'user_logout' },
            success: function (output) {
                window.location.href = window.location.host + "/login";
            }
        });
    });
    
    jQuery('.wrapper-button').click(function(){
        jQuery('.wrapper-overlay').toggle();
    });
    
    jQuery('body').on('submit', '.site_form', function(e){
        e.preventDefault();
        jQuery('#form_error_msgs_container').html('');
        var $form = jQuery(this);
        $form.find('[type="submit"]').find(".animation-sending").append('<iconify-icon icon="svg-spinners:12-dots-scale-rotate"></iconify-icon>');
        $form.addClass('disabled').find('[type="submit"]').prop('disabled', true);
    //grecaptcha.execute(globalVars.recaptcha_key, {action: 'submit'})
        //.then(function (token) {

            var formData = $form.serializeArray();

            formData.push({
                name: "action",
                value: "send_site_forms"
            });
            /*formData.push({
                name: "recaptcha_token",
                value: token
            });*/
            
            if (xhr && xhr.readyState != 4)
                xhr.abort();
                xhr = jQuery.ajax({
                    url: "/wp-admin/admin-ajax.php",//globalVars.ajaxurl,
                    data: formData,
                    dataType: 'json',
                    method: 'POST'
                }).done(function (data) {
                    $form.find('[type = "submit"]').find(".animation-sending").empty();
                    $form.removeClass('disabled').find('[type = "submit"]').prop('disabled', false);
                    var func;
                    if (data.status == 'success') {
                        func = $form.data('success');
                        //window[func]($form, data);
                        reload_page(data);
                    } 
                    else {
                        if (data.status == 'exception') {
                            alert(data.reason);
                        }
                        else if($form.data('failed')) {
                            func = $form.data('failed');
                            window[func]($form, data);
                        } else {
                            alert(data.msg);
                        }
                    }

           }).fail(function (data) {
                $form.find('[type = "submit"]').find(".animation-sending").empty();
                $form.removeClass('disabled').find('[type = "submit"]').prop('disabled', false);
                if(data.responseText){
                    alert(data.responseText)
                }
                else{
                    alert('השליחה נכשלה! שורה 50');
                }

            });
        //});
    //}
});
})

function reload_page(data){
        window.location.href = data.redirect;
}

function ajaxfunction(action,  options){

    var sql = options[0].query;
    var values = options[0].values;
    // var values= options.values || null;
    // var data = { action: action,query: sql,
    //     update_table:options.update_table ,values:values,sub_table_value:options.sub_table_value};
    jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",
            data: { action: action,options:options},
            dataType: 'json',
            method: 'POST'
        }).done(function(result) {
            if(options.sub_table_value){
                jQuery('#sub-table-container .data-grid').remove();
                jQuery('#sub-table-container').append(result.html_tr);
            }
            else if(sql.startsWith("delete")){
                jQuery.each(values,function (key,id){
                    jQuery("input.checkbox-row[value="+id+"]").closest('tr').remove();
                })
            }
            else if(sql.startsWith("update")){
                var updatedTr = jQuery("input.checkbox-row[value="+values[0]+"]").closest('tr');
                updatedTr.empty();
                var newRowHtml =result.html_tr.replace("</tr>","").replace ("<tr>","");
                updatedTr.append(newRowHtml);
            }
            else if(sql.startsWith("insert")){
                jQuery(".central-table tbody").append(result.html_tr);
            }
            var a= result;
            return a;
        }).fail(function(result) {
            if(result.responseText == "run_query ok!"){
                
            }
        })
}

function show_slider_message(textH1, secondaryText, funcOk,options){
    jQuery(".slider-message h1").text(textH1);
    jQuery(".slider-message .secondary-text").text(secondaryText);
    jQuery(".slider-message").show();
    jQuery(".slider-message").attr("display","block");
    //jQuery(".slider-message").animate({display:"block", top:"50%",/*,opacity: "0.95"*/}, 1000);
    jQuery('.slider-message').fadeIn(10);
    jQuery(".slider-message .ok-button").on("click",function(){
        window[funcOk](options);
    })
    
    jQuery(".slider-message .cancel-button").on("click",function(){
        close_slider_message();

    })
}
function close_slider_message(){
    jQuery('.checkbox-row:checked').prop('checked', false);
    jQuery('.choose-all-rows:checked').prop('checked', false);
    jQuery(".slider-message").fadeOut(100);
    //jQuery(".slider-message").animate({display:"none"},1000);
}

function removeRows(options){
    var rows = jQuery('tr:not(:hidden) .checkbox-row:checked');
    var ids = jQuery.map(rows,function(row){ return jQuery(row).val() });
    var sql = "delete from wp_y1_" + jQuery('input[name=table_name]').val() + " where " + jQuery('input[name=id_column]').val() + " in ("+ ids.join(",") +")";
    var sql_arr =[{query:sql,values:ids}];
    ajaxfunction('run_sql', sql_arr);
    close_slider_message();
}

function closePopup(){
    jQuery('html').removeClass('popup_open');
}

function openPopup(source_button){
    jQuery('html').addClass('popup_open');
}

function get_sub_table_checked_row(filterValueToInsert){
        var id_column = jQuery('input[name="id_column"]').val();

        jQuery.ajax({
            dataType: 'json',
            method: 'POST',
            url: "/wp-admin/admin-ajax.php",
            data: 
            {
                page_name:jQuery('input[name="page-name"]').val(),
                parent_id_value:jQuery('.new-row input[name="id_column"]').val(),
                filter_value: filterValueToInsert,
                action: 'get_sub_table_checked_row'
            },
            success: function (output) {
                jQuery("#sub-table-container table").remove();
                jQuery("#sub-table-container").append(output);
            }
        }); 
    }





