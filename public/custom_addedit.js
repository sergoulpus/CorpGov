$(function(){


	
    $('input[name="f[firma][nimi]"]').change(function(){
        $nimi = $(this).val();
        $type = 0;
        if($nimi.indexOf(' AS') != -1 ) {
            $type = 2;
        }
        else if($nimi.indexOf(' OÜ') != -1) {
            $type = 1;
        }
	
        if($type != 0 && $('select[name="f[firma][juriidilineVormFirma_id]"]' ).val() == '0') {
            $('select[name="f[firma][juriidilineVormFirma_id]"]' ).val($type);
        }
	
	
	
    });


    

    $('input[name*="f[firma][noukoguSuurus]"]').change(function(){
        if($(this).val() == 0) {
            $('.fsnoukogu').find('input, textarea, button, select').attr('disabled','disabled').css('color','#bababa').parent().css('color','#bababa');
        }
        else		{
            $('.fsnoukogu').find('input, textarea, button, select').removeAttr('disabled').css('color','black').parent().css('color','black');
        }
        
        });

    $('input[name*="f[firma][noukoguSuurus]"]').change();






});