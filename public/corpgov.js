var catcher = function() {
	var changed = false;
	$('form').each(function() {
		if ($(this).data('initialForm') != $(this).serialize()) {
			changed = true;
			$(this).addClass('changed');
		} else {
			$(this).removeClass('changed');
		}
	});
	if (changed) {
		return 'Oled vahepeal andmeid muutnud! Kas oled kindel, et tahad ilma salvestamata lahkuda';
	}
};

$(function() {
	$('form').each(function() {
		$(this).data('initialForm', $(this).serialize());
	}).submit(function(e) {
		var formEl = this;
		var changed = false;
		$('form').each(function() {
			if (this != formEl && $(this).data('initialForm') != $(this).serialize()) {
				changed = true;
				$(this).addClass('changed');
			} else {
				$(this).removeClass('changed');
			}
		});
		if (changed && !confirm('Another form has been changed. Continue with submission?')) {
			e.preventDefault();
		} else {
			$(window).unbind('beforeunload', catcher);
		}
	});
	$(window).bind('beforeunload', catcher);
});


$(function() {

	$('input').focus(function() {
		$(this).closest('tr').addClass('highlight');
	});
 
	$('input').blur(function() {
		$(this).closest('tr').removeClass('highlight');
	});

	$("input").hover(
		function () {
			$(this).closest('tr').addClass('highlight_hover');
		}, 
		function () {
			$(this).closest('tr').removeClass('highlight_hover');
		}
		);
			
			
			
	/* Vastavalt isiku tüübile peidame ebavajaliku */			


	$('select[name*="juriidilineVormIsik_id"]').change(function(){
		// get isik id class
		$isik_id = $(this).attr('name');
		$isik_id = $isik_id.replace('[juriidilineVormIsik_id]', ""); 
		$isik_id = $isik_id.replace(/\[/g, ""); 
		$isik_id = $isik_id.replace(/\]/g, ""); 


		if($(this).val() == 1) {
			$('.fsjuhatus.'+$isik_id).add('.fsnoukogu.'+$isik_id).find('input, textarea, button, select').attr('disabled','disabled').css('color','#bababa').parent().css('color','#bababa');
		}
		else		{
			$('.fsjuhatus.'+$isik_id).add('.fsnoukogu.'+$isik_id).find('input, textarea, button, select').removeAttr('disabled').css('color','black').parent().css('color','black');
		}

	});

	$('select[name*="juriidilineVormIsik_id"]').change();



	/* Notifications */ 

	$message = $('#statusMsg').text();
		
	if($message.length > 0) {
		$type=$('#statusMsg').attr('type');
		if($type == "error") {
			$delay = 10*1000;
		}
		else {
			$delay = 2*1000;
		}
		
		$.jnotify($message, $type, $delay);
	} 







//	f[isik][null][juriidilineVormIsik_id]

// TODO: et töötaks ainult konkreetsel isikul

/*
	$('select[name*="juriidilineVormIsik_id"]').change(function(){
		// 0 = füüsiline isik
		// 1 = juriidiline isik
		if($(this).val() == 1) {
			$('fieldset.juhatus table, fieldset.noukogu table').css("visibility","hidden");
			$('fieldset.juhatus, fieldset.noukogu').css("height","0px");
			
			
		}
		else {
			$('fieldset.juhatus table, fieldset.noukogu table').css("visibility","visible");
			$('fieldset.juhatus,fieldset.noukogu ').css("height","auto");
		}
	});

	 */		
  
});