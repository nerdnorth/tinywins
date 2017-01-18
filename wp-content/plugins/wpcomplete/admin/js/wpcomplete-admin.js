(function( $ ) {
	'use strict';

  // PREMIUM: Add color picker widget to all fields with .color-picker class:
  $('.color-picker').wpColorPicker();

	// PREMIUM: Completion URL autocomplete:
	var cache = {};
  $( "#completion_redirect_to" ).autocomplete({
    delay: 500,
    source: function( request, response ) {
      var term = request.term;
      if ( term in cache ) {
        response( cache[ term ] );
        return;
      }

      $.getJSON( WPComplete.url + "?action=post_lookup", request, function( data, status, xhr ) {
        cache[ term ] = data;
        response( data );
      });
    },
    select: function( event, ui ) {
      $( '#completion_redirect_url' ).val(ui.item.link);
      $( "#completion_redirect_to" ).val( ui.item.label );
      return false;
    }
  }).on('keyup', function(e) {
  	if (e.target.value == '') {
	    $( '#completion_redirect_url' ).val('');
  	}
  }); 

	// we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;
	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

	  // "call" the original WP edit function
	  // we don't want to leave WordPress hanging
	  $wp_inline_edit.apply( this, arguments );

	  // now we take care of our business

	  // get the post ID
	  var $post_id = 0;
	  if ( typeof( id ) == 'object' )
	    $post_id = parseInt( this.getId( id ) );

	  if ( $post_id > 0 ) {

	    // define the edit row
	    var $edit_row = $( '#edit-' + $post_id );

	    // get the completable button status
			var $completable = $( '#completable-' + $post_id ).text();

			// populate the completable button status
			$edit_row.find( 'input[name="wpcomplete[completable]"]' ).attr( 'checked', $completable != '—' );

	  }

	};

})( jQuery );
