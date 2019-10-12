(function( $ ) {	
	jQuery(document).ready( function() {
		'use strict';
        var cs__image_frame, image_data;
		$(function() {
			if ( undefined !== cs__image_frame ) {
				cs__image_frame.open(); return;
			}
		});
	});
	
	jQuery(document).on('click', '.wormvc_img_select', function(e) {
		var clickimagen=event.target;
		cs__image_frame = wp.media.frames.cs__image_frame = wp.media({
			title: $(clickimagen).attr("data-title"),               
			multiple: false
		});
		cs__image_frame.on( 'select', function() {
			image_data = cs__image_frame.state().get( 'selection' ).first().toJSON();
			if(image_data['id'] && image_data['url']){
				$(clickimagen).parent('div').find('.wormvc_img_id').val(image_data['id']);
                //console.log($(clickimagen).parent('div').find('.cs__imgid').val());
				$(clickimagen).parent('div').find('.wormvc_img_img').html("<img src='"+image_data['url']+"' style='max-width:360px; max-height:200px;' >");
				$(clickimagen).parent('div').find('.wormvc_img_del').css('display', 'inline-block');
			}
		});					
		cs__image_frame.open();		
	});
	
	jQuery(document).on('click', '.wormvc_img_del', function(e) {
		$(this).parent('div').find('.wormvc_img_img').empty();
		$(this).parent('div').find('input[type=text]').val('');
		$(this).css('display', 'none');
	});
})( jQuery );

jQuery( document ).ready( function( $ ) {
	tinymce.init( {
		selector: '.wormvc_mce',
		mode : "specific_textareas",
		editor_selector : ".wormvc_mce",
		elements : 'pre-details',
		height : "320px",
		theme: "modern",
		skin: "lightgray",
		menubar : false,
		statusbar : false,
		plugins : "paste",
		paste_auto_cleanup_on_paste : true,
		paste_postprocess : function( pl, o ) {
			o.node.innerHTML = o.node.innerHTML.replace( /&nbsp;+/ig, " " );
		}
	} );
} );

jQuery(document).ready(function($){
    jQuery('.wormvc_color_picker').wpColorPicker();
});
