(function( $ ) {
 
    $(document).ready(function(){

        $('#ltrav-bg-color, #ltrav-font-color').wpColorPicker();

        $('.wp-picker-container').each(function(){

            $(this).parents('tr').addClass('hide-if-random');

        });

        $('.random-lock').parents('tr').addClass('show-if-random');

        if ($('.randomize').is(':checked')) {

            $('.show-if-random').css('display','table-row');
            $('.hide-if-random').css('display','none');

        } else {

            $('.show-if-random').css('display','none');
            $('.hide-if-random').css('display','table-row');

        }

    });

    $('.randomize').on('change', function() {

        if ($(this).is(':checked')) {

            $('.show-if-random').css('display','table-row');
            $('.hide-if-random').css('display','none');

        } else {

            $('.show-if-random').css('display','none');
            $('.hide-if-random').css('display','table-row');

        }

    });

    $('#ltrav-gfont-select').select2({
        placeholder: "Select a font",
    });

    $('#ltrav-gfont-select').on('select2:select',function(evt){

        //console.log(evt);

    	var font_name = evt.params.data.id;

    	var variants = $('#ltrav-gfont-select option[value="'+font_name+'"]').attr('data-var');
    	variants = variants.split(',');

    	$('#ltrav-gfont-style').html('');

    	$.each(variants,function(){
    		$('#ltrav-gfont-style').append($('<option />').val(this).text(this));
    	});


    });
     
})( jQuery );