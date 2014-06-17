jQuery(document).ready(function($){
	var calendar_image = '';
	if (typeof woocommerce_writepanel_params != 'undefined'){
		calendar_image = woocommerce_writepanel_params.calendar_image;
	} else if (typeof woocommerce_admin_meta_boxes != 'undefined'){
		calendar_image = woocommerce_admin_meta_boxes.calendar_image;
	}
	
	jQuery('.datetimepicker').datetimepicker(
		{defaultDate: "",
		dateFormat: "yy-mm-dd",
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: calendar_image,
		buttonImageOnly: true
		});	
		
	var productType = jQuery('#product-type').val();
	if (productType=='auction'){
		jQuery('.show_if_simple').show();
		jQuery('.inventory_options').hide();
	}
	jQuery('#product-type').live('change', function(){
		if  (jQuery(this).val() =='auction'){
			jQuery('.show_if_simple').show();
			jQuery('.inventory_options').hide();
		}
	});
	jQuery('#_virtual').addClass('show_if_auction');
	jQuery('label[for="_virtual"]').addClass('show_if_auction');
	jQuery('#_downloadable').addClass('show_if_auction');
	jQuery('label[for="_downloadable"]').addClass('show_if_auction');
	
	jQuery('.auction-table .action a').on('click',function(event){
		var logid = $(this).data('id');
		var postid = $(this).data('postid');
		var curent = $(this);
		
		jQuery.ajax({
         type : "post",
         url : SA_Ajax.ajaxurl,
         data : {action: "delete_bid", logid : logid, postid: postid, SA_nonce : SA_Ajax.SA_nonce },
         success: function(response) {
         			if (response == 'deleted'){
         				curent.parent().parent().addClass('deleted').fadeOut('slow');
         			}
                     
        	}
      	});
      	event.preventDefault();
      	
	});
	jQuery('#general_product_data #_regular_price').live('keyup',function(){
      	jQuery('#auction_tab #_regular_price').val(jQuery(this).val());
   	});
});