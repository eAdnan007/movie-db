jQuery(document).ready(function($){
	$('#official-sites-input-list').addInputArea({
		area_var: 'li',
		btn_add: '.add-site',
		btn_del: '.remove-site'
	});
	$('#crew-list').addInputArea({
		area_var: 'tr',
		btn_add: '.add-crew',
		btn_del: '.remove-crew',
		after_add: function(){
			$('#crew-list tbody tr:last-child .thumb img').attr('src', mdb.mistryman );
			// $( ".mdb-profile" ).autocomplete( "destroy" );
			set_autocomplete_profile();
		}
	});
	$('#cast-list').addInputArea({
		area_var: 'tr',
		btn_add: '.add-artist',
		btn_del: '.remove-artist',
		after_add: function(){
			$('#cast-list tbody tr:last-child .thumb img').attr('src', mdb.mistryman );
			// $( ".mdb-profile" ).autocomplete( "destroy" );
			set_autocomplete_profile();
		}
	});
	$('#known-for-list').addInputArea({
		area_var: 'tr',
		btn_add: '.add-movie',
		btn_del: '.remove-movie',
		after_add: function(){
			$('#known-for-list tbody tr:last-child .thumb img').attr('src', mdb.defaultposter );
			// $( ".mdb-profile" ).autocomplete( "destroy" );
			set_autocomplete_movie();
		}
	});


	var set_autocomplete_movie = function(){
		$( ".mdb-movie").autocomplete({
			source: mdb.ajax_url + '?action=get-movies',
			minLength: 3,
			delay: 500,
			autoFocus: true,
			focus: function( event, ui ) {
				$(this).val( ui.item.label );
				$(this).parent().parent().children('.thumb').children('img').attr('src', ui.item.thumb);
				$(this).parent().parent().children('.movie_list_resizer, .cast_list_resizer').children('.movie_id').val(ui.item.ID);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			},
			search: function(){
				$(this).parent().parent().children('.thumb').children('img').attr('src', mdb.mistryman);
				$(this).parent().parent().children('.movie_list_resizer').children('.movie_id').val( 0 );
			}
		})
		._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( "<a>" + item.label + "</a>" )
			.appendTo( ul );
		}
	}

	var set_autocomplete_profile = function(){
		$( ".mdb-profile" ).autocomplete({
			source: mdb.ajax_url + '?action=get-profiles',
			minLength: 3,
			delay: 500,
			autoFocus: true,
			focus: function( event, ui ) {
				$(this).val( ui.item.label );
				$(this).parent().parent().children('.thumb').children('img').attr('src', ui.item.thumb);
				$(this).parent().parent().children('.crew_list_resizer, .cast_list_resizer').children('.profile_id').val(ui.item.ID);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			},
			search: function(){
				$(this).parent().parent().children('.thumb').children('img').attr('src', mdb.mistryman);
				$(this).parent().parent().children('.crew_list_resizer, .cast_list_resizer').children('.profile_id').val( 0 );
			}
		})
		._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( "<a>" + item.label + "</a>" )
			.appendTo( ul );
		}
	}

	set_autocomplete_profile();
	set_autocomplete_movie();
});