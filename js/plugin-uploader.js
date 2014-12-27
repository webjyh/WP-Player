/**
 * @name     Wp-Player Admin JS
 * @desc     MetaBox JavaScript
 * @depend   jQuery
 * @author   M.J
 * @date     2014-12-19
 * @update   2014-12-27
 * @URL      http://webjyh.com
 * @version  2.1.0
 * 
 */
jQuery(document).ready(function() {
	
	//wp-player upload dialog
	var original_send_to_editor = window.send_to_editor;
	jQuery('.WP-Player-File').on('click', function() {
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
	
	//wp-player Tabs
	jQuery('#wp-player-tabs > li').on('click', function(){
		var index = jQuery(this).index();
		jQuery(this).addClass('current').siblings().removeClass('current');
		jQuery('#wp-player-row > div').hide().eq(index).fadeIn();
	});
	
	//get Xiami ID
	jQuery('#wp_player_get_xiami_id').on('click', function(){
		var $elem = jQuery('#mp3_xiami'),
			$select = jQuery('#mp3_xiami_type'),
		    $val = $elem.val(),
		    reg = /^http[s]?:\/\/\w*[\.]?xiami.com+\/(\w+)\/+(\d+).*$/,
		    xiami = {};

		if ( typeof $val === 'undefined' ||  $val == '' ){
			$elem.focus();
		}

		var result = $val.match(reg);
		if (jQuery.isArray( result )){
			xiami['type'] = result[1];
			xiami['id'] = result[2];
		} else {
			if (!jQuery.isNumeric($val)){
				alert('获取虾米音乐ID失败！')
			}
		}

		if ( jQuery.isArray( result ) && xiami['type'] && xiami['id'] ){
			$elem.val( xiami['id'] );
			$select.children('option').prop('selected', false);
			jQuery('#mp3_xiami_type').find('option[value='+xiami['type']+']').prop('selected', true);
		}
	});
});