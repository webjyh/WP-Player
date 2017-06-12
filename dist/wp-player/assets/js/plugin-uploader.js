/*!
 * @name     Wp-Player Admin JS
 * @desc     MetaBox JavaScript
 * @depend   jQuery
 * @author   M.J
 * @date     2014-12-19
 * @update   2017-6-12
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @version  2.6.1
 * 
 */
jQuery(document).ready(function(){var e=window.send_to_editor;jQuery(".WP-Player-File").on("click",function(){var i=jQuery(this).prev(),t=i.attr("id");return tb_show("","media-upload.php?media-upload.php?type=image&amp;TB_iframe=true"),window.send_to_editor=function(r){var a=jQuery(r).attr("href"),o=i.val();tb_remove(),jQuery("#"+t).val(o+"\r"+a),window.send_to_editor=e},!1}),jQuery("#wp-player-tabs > li").on("click",function(){var e=jQuery(this).index();jQuery(this).addClass("current").siblings().removeClass("current"),jQuery("#wp-player-row > div").hide().eq(e).fadeIn()}),jQuery("#wp_player_get_xiami_id").on("click",function(){var e=jQuery("#wp_player_music_type"),i=jQuery("#mp3_xiami"),t=jQuery("#mp3_xiami_type"),r=i.val(),a=[/^http[s]?:\/\/music.163.com\/#.*\/{1}(.+)\?id=(\d+)/,/^http[s]?:\/\/\w*[\.]?xiami.com+\/(\w+)\/+(\w+).*/,/^http[s]?:\/\/y.qq.com\/n\/yqq\/(.+)\/+(\w+)*/,/^http[s]?:\/\/music.baidu.com\/(.+)\/+(\w+)*/],o=["netease","xiami","tencent","baidu"],n={};if(void 0===typeof r||""==r)return i.focus(),!1;var p=r.indexOf("163.com")>-1?0:r.indexOf("xiami.com")>-1?1:r.indexOf("y.qq.com")>-1?2:3,y=r.match(a[p]);if(!y||!jQuery.isArray(y))return alert("获取音乐ID失败！"),!1;jQuery.isArray(y)&&(n.type=y[1],n.id=y[2]),jQuery.isArray(y)&&n.type&&n.id&&("singer"==n.type&&(n.type="artist"),"playlist"!=n.type&&"songlist"!=n.type||(n.type="collect"),i.val(n.id),e.find("option[value="+o[p]+"]").prop("selected",!0),t.find("option[value="+n.type+"]").prop("selected",!0))})});