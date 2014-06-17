jQuery(document).ready(function($){
	$( ".auction-time-countdown" ).each(function( index ) {
		var time 	= $(this).data('time');
		var format 	= $(this).data('format');
		
		if(format == ''){
			format = 'yowdHMS';
		}
		$(this).countdown({
			until:   $.countdown.UTCDate(-(new Date().getTimezoneOffset()),new Date(time*1000)),
			format: format, 
			
			onExpiry: closeAuction,
			expiryText: '<div class="over">'+data.finished+'</div>'
		});
			 
	});
});

function closeAuction(){
		var auctionid = jQuery(this).data('auctionid');
		var ajaxcontainer = jQuery(this).parent().next('.auction-ajax-change');
		
		ajaxcontainer.empty().prepend('<div class="ajax-working"></div>');
		ajaxcontainer.parent().children('form.buy-now').remove();
		
		 jQuery.ajax({
         type : "post",
         url : SA_Ajax.ajaxurl,
         data : {action: "finish_auction", post_id : auctionid, ret: ajaxcontainer.length},
         success: function(response) {
         			if (response != 0){
         				ajaxcontainer.children('.ajax-working').remove();
         				ajaxcontainer.prepend(response);
         			}
                     
        	}
      	});
		
}
