// jSale 1.431
// http://jsale.biz

//jQuery.noConflict()
jQuery(document).ready(function($) {

	$('body').on('click', '.jSaleOrder', function(e) {
		var form_id = $(this).parent().children('.jSaleWindow').find('input[name="id_form"]').val();
		$(this).parent().children('.jSaleWindow').simple_modal({onOpen: modalOpen, onClose: simplemodal_close, autoResize: true, /*overlayClose: true*/});
	});
			
	$('body').on('keyup', '.jSaleQty', function(e) {
		e.preventDefault();

		var newQty = $(this).val();
		var form = $(this).parents('.jSaleForm');
		var form_type = form.find('[name="form_type"]').val();

		if (newQty && e.which !== 9) {
			var updateTimer = window.setTimeout(function() {
				var antispam = form.find('[name="order_spam"]').val();
				form.find('[name="order_nospam"]').val(antispam);
				/*$.ajax({
					url: form.attr('action'),
					data: form.serialize() + '&action=discount',
					type: 'POST',
					success: function(response) {
						if (form_type == 'form')
							form.parents('.jSaleWrapper').html(response);
						else
							$('.simplemodal-wrap').html(response);
					},
					error: function() {
						alert('Error sending request!');
					}
				});*/
			}, 500);
		}
	});
	
	$('body').on('keyup', '.jSaleQty2Form', function(e) {
		
		var newQty = $(this).val();
		var form = $(this).parents('.jSale').find('.jSaleForm');
		form.find('.jSaleQty').val(newQty);
		
		if (newQty) {
			var updateTimer = window.setTimeout(function() {
				var antispam = form.find('[name="order_spam"]').val();
				form.find('[name="order_nospam"]').val(antispam);
				$.ajax({
					url: form.attr('action'),
					data: form.serialize() + '&action=discount',
					type: 'POST',
					success: function(response) {
							form.parents('.jSaleWindow').html(response);
					},
					error: function() {
						alert('Error sending request!');
					}
				});
			}, 100);
		}
		
		$(this).keydown(function(e) {
			if (e.which !== 9) {
				window.clearTimeout(updateTimer);
			}
		});
	});
	
	$('body').on('click', '.jSaleQtyBtn', function(e) {
		e.preventDefault();
		var form = $(this).parents('.jSaleForm');
		var form_type = form.find('[name="form_type"]').val();
		var qty_input = form.find('.jSaleQty');
		var qty = parseFloat(qty_input.val());
		
		if ($(this).hasClass('jSaleQtyMinus') && qty > 1) {
			newQty = qty - 1;
		}
		else if ($(this).hasClass('jSaleQtyPlus')) {
			newQty = qty + 1;
		}
		else
			newQty = false;

		
		if (newQty) {
			qty_input.val(newQty);
		
			var updateTimer = window.setTimeout(function() {
				var antispam = form.find('[name="order_spam"]').val();
				form.find('[name="order_nospam"]').val(antispam);
				/*$.ajax({
					url: form.attr('action'),
					data: form.serialize() + '&action=discount',
					type: 'POST',
					success: function(response) {
						if (form_type == 'form')
							form.parents('.jSaleWrapper').html(response);
						else
							$('.simplemodal-wrap').html(response);
					},
					error: function() {
						alert('Error sending request!');
					}
				});*/
			}, 300);
		}
		
		$(this).keydown(function(e) {
			if (e.which !== 9) {
				window.clearTimeout(updateTimer);
			}
		});
	});
	
	$('body').on('keyup', '.jSaleCode', function(e) {

		var newCode = $(this).val();
		var form = $(this).parents('.jSaleForm');
		var form_type = form.find('[name="form_type"]').val();
		
		//if (newCode) {
			var updateTimer = window.setTimeout(function() {
				var antispam = form.find('[name="order_spam"]').val();
				form.find('[name="order_nospam"]').val(antispam);
				$.ajax({
					url: form.attr('action'),
					data: form.serialize() + '&action=discount',
					type: 'POST',
					success: function(response) {
						if (form_type == 'form')
							form.parents('.jSaleWrapper').html(response);
						else
							$('.simplemodal-wrap').html(response);
					},
					error: function() {
						alert('Error sending request!');
					}
				});
			}, 1000);
		//}
		
		$(this).keydown(function(e) {
			if (e.which !== 9) {
				window.clearTimeout(updateTimer);
			}
		});
	});
	
	$('body').on('click', '.modalCloseImg', function(e) {
		e.preventDefault();
		var a = $(this).parent().find('.jSaleCall');
		if (a.length != 0) {
			var form = a;
			var antispam = form.find('[name="order_spam"]').val();
			var form_type = form.find('[name="form_type"]').val();
			form.find('[name="order_nospam"]').val(antispam);
			
			$.ajax({
				url: form.attr('action'),
				data: form.serialize() + '&action=send',
				type: 'POST',
				success: function(response) {
					$.simple_modal.close();
				},
				error: function() {
					alert('Error sending request!');
				}
			});
		}
	});
	
	$('body').on('click', '.jSaleSubmit', function (e) {
		e.preventDefault();
		var form = $(this).parents('.jSaleForm');
		var antispam = form.find('[name="order_spam"]').val();
		var feedback = form.find('[name="feedback"]').val();
		var call = form.find('[name="call"]').val();
		var form_type = form.find('[name="form_type"]').val();
		var submit = $(this).attr('name');
		form.find('[name="order_nospam"]').val(antispam);
		
		$(this).attr('disabled', 'disabled');
		$(this).val('Sending...');

		$.ajax({
			url: form.attr('action'),
			data: form.serialize() + '&action=send&submit=' + submit,
			type: 'POST',
			success: function(response) {
				if (form_type == 'form')
					form.parents('.jSaleWrapper').html(response);
				else {
					$('.simplemodal-wrap').html(response);
					$('#simplemodal-container').css('height', 'auto');
					
					//alert($('#simplemodal-container').height());
					//alert($(window).height());
					//alert($(window).width());

					if ($(window).width() < 500 || $('#simplemodal-container').height() > $(window).height()) {
						$('#simplemodal-container').css('position', 'absolute');
						$('#simplemodal-container').css('top', '0px');
						$('#simplemodal-container').css('height', $(window).height() * 5);
						//window.scrollTo(0,0);
					}
					//$(window).trigger('resize.simplemodal');
				}
				
				if (response.indexOf('<!--order_send-->') + 1)
				{
					setTimeout(function() { $.simple_modal.close() }, 2000);
					if (window.redirect && !feedback && !call)
						setTimeout(function() { window.location.replace(redirect) }, 3000);
					else if (window.feedback_redirect && feedback)
						setTimeout(function() { window.location.replace(feedback_redirect) }, 3000);
					else if (window.call_redirect && call)
						setTimeout(function() { window.location.replace(call_redirect) }, 3000);
				}
			},
			error: function() {
				alert('Error sending request!');
			}
		});
	});
});

