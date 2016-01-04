(function( $ ) {
 
    $(document).ready(function(){
        $('#ltrav-bg-color, #ltrav-font-color').wpColorPicker();
    });

    $('#ltrav-gfont-select').chosen();

    $('#ltrav-gfont-select').on('change',function(evt,params){
    	var font_name = params.selected;

    	var variants = $('#ltrav-gfont-select option[value="'+font_name+'"]').attr('data-var');
    	variants = variants.split(',');

    	$('#ltrav-gfont-style').html('');

    	$.each(variants,function(){
    		$('#ltrav-gfont-style').append($('<option />').val(this).text(this));
    	});


    });
     
})( jQuery );