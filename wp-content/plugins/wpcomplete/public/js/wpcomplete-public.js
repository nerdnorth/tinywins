(function( $ ) {
	'use strict';

	var $this;
	
	$(function() {
		// add click event handlers for plugin completion buttons...
		$('body').on('click', 'a.wpc-button-complete', function(e) {
			e.preventDefault();
			$this = $(this);
			var post_id = $(this).data('id');
			// change button to disable and indicate saving...
			$this.attr('disabled', 'disabled').find('span').toggle();
			$.ajax({
				url: wpcompletable.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					_ajax_nonce: wpcompletable.nonce,
					action: 'mark_completed',
					post_id: post_id
				},
				success: function(response) {
					//console.log(response);
					for (var x in response) {
						if (''+x == 'this') {
							$this.replaceWith(response[x]);
						} else if (''+x == 'redirect') {
							window.location.href = response[x];
						// PREMIUM: 
						} else if (''+x.indexOf('[data-') >= 0) {
							var d = x.substring(x.indexOf('[data-')+1, x.indexOf(']'));
							$(''+x).attr(d, response[x]);
						} else if (''+x.indexOf('data-') == 0) {
							$('['+x+']').attr(''+x, response[x]);
						} else {
							$(''+x).replaceWith(response[x]);
						}
					}
					$('a.wpc-lesson-' + post_id).addClass('wpc-lesson-completed');
					$('a.wpc-lesson-' + post_id).removeClass('wpc-lesson-complete');
				},
				error: function(xhr, textStatus, errorThrown) {
					$this.attr('disabled', false).html('Error');
		      alert("Uh oh! We ran into an error marking the lesson as completed.");
		      console.log(textStatus);
		      console.log(errorThrown);
		    }
			});
			return false;
		});
		$('body').on('click', 'a.wpc-button-completed', function(e) {
			e.preventDefault();
			$this = $(this);
			var post_id = $(this).data('id');
			// change button to disable and indicate saving...
			$this.attr('disabled', 'disabled').find('span').toggle();
			$.ajax({
				url: wpcompletable.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					_ajax_nonce: wpcompletable.nonce,
					action: 'mark_uncompleted',
					post_id: post_id
				},
				success: function(response) {
					//console.log(response);
					for (var x in response) {
						if (''+x == 'this') {
							$this.replaceWith(response[x]);
						} else if (''+x == 'redirect') {
							window.location.href = response[x];
						// PREMIUM: 
						} else if (''+x.indexOf('[data-') >= 0) {
							var d = x.substring(x.indexOf('[data-')+1, x.indexOf(']'));
							$(''+x).attr(d, response[x]);
						} else if (''+x.indexOf('data-') == 0) {
							$('['+x+']').attr(''+x, response[x]);
						} else {
							$(''+x).replaceWith(response[x]);
						}
					}
					$('a.wpc-lesson-' + post_id).addClass('wpc-lesson-complete');
					$('a.wpc-lesson-' + post_id).removeClass('wpc-lesson-completed');
				},
				error: function(xhr, textStatus, errorThrown) {
					$this.attr('disabled', false).html('Error');
		      alert("Uh oh! We ran into an error marking the lesson as no longer complete.");
		      console.log(textStatus);
		      console.log(errorThrown);
		    }
			});
			return false;
		});

		// PREMIUM:
		// Do ajax call to backend to get a list of ALL the completable lessons.
		// Then filter through each link on the page and add specific classes to completed and incomplete links.
		$.ajax({
			url: wpcompletable.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				_ajax_nonce: wpcompletable.nonce,
				action: 'get_completable_list'
			},
			success: function(response) {
				//console.log(response);
				$('a[href]').each(function() {
					if (response[$(this).attr('href')] !== undefined) {
						//console.log("MATCH!!!");
						$(this).addClass('wpc-lesson');
						if (response[$(this).attr('href')] && response[$(this).attr('href')]['id']) {
							$(this).addClass('wpc-lesson-' + response[$(this).attr('href')]['id']);
						}
						if (response[$(this).attr('href')] && response[$(this).attr('href')]['completed']) {
							$(this).addClass('wpc-lesson-completed');
						} else {
							$(this).addClass('wpc-lesson-complete');
						}
					}
				});
			},
			error: function(xhr, textStatus, errorThrown) {
				console.log(textStatus);
	      console.log(errorThrown);
	    }
		});
	});

})( jQuery );
