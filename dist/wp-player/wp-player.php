<?php
/*
 * Plugin Name: WP-Player
 * Plugin URI: http://webjyh.com/wp-player/
 * Description: WP-Player 是一个迷你歌曲播放器，支持多歌曲播放，支持使用网易云音乐, 虾米音乐, QQ音乐, 百度音乐歌曲地址, 也支持自定义上传音乐。
 * Version: 2.6.2
 * Author: M.J
 * Author URI: http://webjyh.com
 * License: GPLv2 or later
*/
class_exists('wp_player_plugin') || require_once('include/player.php');
new wp_player_plugin();
?>