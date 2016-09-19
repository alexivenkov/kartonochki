// jSale 1.431
// http://jsale.biz

jQuery(document).ready(function($) {

	var key = 0;
	$('.jSale').each(function() {
		key++;
		$(this).load(sitelink + dir + 'jsale.php', { product : products[key], id: key, cookies: cookies });
		
		if (products[key]['open_modal'])
		{
			setTimeout(function() {
				$('#jsale_form_'+key).parent().parent().find('.jSaleOrder').trigger('click');
			}, 1000);
		}
	});

	$('.jSaleFeedback').load(sitelink + dir + 'feedback/feedback.php', {form_type: feedback_form_type, template: feedback_template});
	
	$('.jSaleCall').load(sitelink + dir + 'call/call.php', {form_type: call_form_type, template: call_template});

	$('.jSaleProducts').load(sitelink + dir + 'jsale2.php', { category : category, data : data });
});