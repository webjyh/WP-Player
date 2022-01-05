/*!
 * @name     Wp-Player Admin JS
 * @desc     MetaBox JavaScript
 * @depend   jQuery
 * @author   M.J
 * @date     2014-12-19
 * @update   2022-1-5
 * @URL      http://webjyh.com
 * @Github   https://github.com/webjyh/WP-Player
 * @version  2.6.2
 * 
 */
jQuery(document).ready(function(){var a=window.send_to_editor;jQuery(".WP-Player-File").on("click",function(){var r=jQuery(this).prev(),t=r.attr("id");return tb_show("","media-upload.php?media-upload.php?type=image&amp;TB_iframe=true"),!(window.send_to_editor=function(e){var i=jQuery(e).attr("href"),e=r.val();tb_remove(),jQuery("#"+t).val(e+"\r"+i),window.send_to_editor=a})}),jQuery("#wp-player-tabs > li").on("click",function(){var e=jQuery(this).index();jQuery(this).addClass("current").siblings().removeClass("current"),jQuery("#wp-player-row > div").hide().eq(e).fadeIn()}),jQuery("#wp_player_get_xiami_id").on("click",function(){var e=jQuery("#wp_player_music_type"),i=jQuery("#mp3_xiami"),r=jQuery("#mp3_xiami_type"),t=i.val(),a={};if(""==t)return i.focus(),!1;var n=-1<t.indexOf("163.com")?0:-1<t.indexOf("xiami.com")?1:-1<t.indexOf("y.qq.com")?2:3,t=t.match([/^http[s]?:\/\/music.163.com\/#.*\/{1}(.+)\?id=(\d+)/,/^http[s]?:\/\/\w*[\.]?xiami.com+\/(\w+)\/+(\w+).*/,/^http[s]?:\/\/y.qq.com\/n\/yqq\/(.+)\/+(\w+)*/,/^http[s]?:\/\/music.baidu.com\/(.+)\/+(\w+)*/][n]);if(!t||!jQuery.isArray(t))return alert("获取音乐ID失败！"),!1;jQuery.isArray(t)&&(a.type=t[1],a.id=t[2]),jQuery.isArray(t)&&a.type&&a.id&&("singer"==a.type&&(a.type="artist"),"playlist"!=a.type&&"songlist"!=a.type||(a.type="collect"),i.val(a.id),e.find("option[value="+["netease","xiami","tencent","baidu"][n]+"]").prop("selected",!0),r.find("option[value="+a.type+"]").prop("selected",!0))})});