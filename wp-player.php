<?php
/*
 * Plugin Name: WP-Player
 * Plugin URI: http://webjyh.com/wp-player/
 * Description: 一个迷你歌曲播放器，可以自己上传MP3，也可以使用虾米歌曲ID，理论支持格式mp3,m4a
 * Version: 1.3.2
 * Author: M.J
 * Author URI: http://webjyh.com
 * License: GPLv2 or later
*/
class_exists('wp_player_plugin') || require_once('include/player.php');
new wp_player_plugin();
?>