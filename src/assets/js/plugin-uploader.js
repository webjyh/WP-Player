/*!
 * @name     Wp-Player Admin JS
 * @desc     MetaBox JavaScript
 * @depend   jQuery
 * @author   M.J
 * @date     2014-12-19
 * @update   <%=date%>
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @version  <%=version%>
 * 
 */
jQuery(document).ready(function() {

    //wp-player upload dialog
    var original_send_to_editor = window.send_to_editor;
    jQuery('.WP-Player-File').on('click', function() {
        var $elem = jQuery(this).prev(),
            formField = $elem.attr('id');
            
        tb_show('', 'media-upload.php?media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function(html) {
            var fileUrl = jQuery( html ).attr('href'),
                originVal = $elem.val();
            
            tb_remove();
            jQuery( '#'+formField ).val( originVal + "\r" +fileUrl );
            window.send_to_editor = original_send_to_editor;
        }
        return false;
    });

    //wp-player Tabs
    jQuery('#wp-player-tabs > li').on('click', function() {
        var index = jQuery(this).index();
        jQuery(this).addClass('current').siblings().removeClass('current');
        jQuery('#wp-player-row > div').hide().eq(index).fadeIn();
    });

    //get Music ID
    jQuery('#wp_player_get_xiami_id').on('click', function() {
        var $type = jQuery('#wp_player_music_type'),
            $elem = jQuery('#mp3_xiami'),
            $select = jQuery('#mp3_xiami_type'),
            $val = $elem.val(),
            regs  = [
                /^http[s]?:\/\/music.163.com\/#.*\/{1}(.+)\?id=(\d+)/,
                /^http[s]?:\/\/\w*[\.]?xiami.com+\/(\w+)\/+(\w+).*/,
                /^http[s]?:\/\/y.qq.com\/n\/yqq\/(.+)\/+(\w+)*/,
                /^http[s]?:\/\/music.baidu.com\/(.+)\/+(\w+)*/
            ],
            typeArr = ['netease', 'xiami', 'tencent', 'baidu'],
            result = {};

        if (typeof $val === undefined ||  $val == '') {
            $elem.focus();
            return false;
        }

        var index = $val.indexOf('163.com') > -1 ? 0 : ($val.indexOf('xiami.com') > -1 ? 1 : ($val.indexOf('y.qq.com') > -1 ? 2 : 3)),
            row = $val.match(regs[index]);

        if (!row || !jQuery.isArray(row)) {
            alert('\u83b7\u53d6\u97f3\u4e50ID\u5931\u8d25\uff01');
            return false;
        }

        if (jQuery.isArray(row)) {
            result['type'] = row[1];
            result['id'] = row[2];
        }

        if ( jQuery.isArray( row ) && result['type'] && result['id'] ) {
            if (result['type'] == 'singer') {
                result['type'] = 'artist';
            }
            if ( result['type'] == 'playlist' || result['type'] == 'songlist' ) {
                result['type'] = 'collect';
            }
            $elem.val( result['id'] );
            $type.find('option[value='+typeArr[index]+']').prop('selected', true);
            $select.find('option[value='+result['type']+']').prop('selected', true);
        }
    });
});