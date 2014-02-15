jQuery(document).ready(function() {

	var original_send_to_editor = window.send_to_editor;

	jQuery('.WP-Player-File').click(function() {
		formField = jQuery(this).prev().attr('id');
		tb_show('', 'media-upload.php?media-upload.php?type=image&amp;TB_iframe=true');
		
		window.send_to_editor = function(html){
			fileUrl = jQuery( html ).attr('href');
			jQuery( '#'+formField ).val( fileUrl );
			tb_remove();
			window.send_to_editor = original_send_to_editor;
		}
		
		return false;
		
	});

	var a = [0,1,2],
		b = [0,1,3,4,5,6],
		v = jQuery('input:radio[name="wp_player_type"]:checked').val();
		
	wp_player_tabs( v );
	
	jQuery('input:radio[name="wp_player_type"]').click(function(){
		var _eq = jQuery('input:radio[name="wp_player_type"]').index(this);
		wp_player_tabs( _eq );
	});
	
	function wp_player_tabs( num ){
		jQuery('#wp-player-tabs tr').hide();
		if ( num == 0 ){
			for ( var i=0; i<b.length; i++ ){
				jQuery('#wp-player-tabs tr').eq(a[i]).show(400);
			}
		} else {
			for ( var i=0; i<b.length; i++ ){
				jQuery('#wp-player-tabs tr').eq(b[i]).show(400);
			}
		}
	}
});