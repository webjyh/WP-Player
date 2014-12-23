<?php

/* Add a new meta box to the admin menu. */
add_action( 'admin_menu', 'wp_player_create_meta_box' );

/* Saves the meta box data. */
add_action( 'save_post', 'wp_player_save_meta_data' );

/**
 * Function for adding meta boxes to the admin.
 * Separate the post and page meta boxes.
 */

function wp_player_create_meta_box() {
	global $theme_name;
	add_meta_box( 'wp-post-meta-boxes', 'WP-Player 播放器选项', 'wpPlayer_post_meta_boxes', 'post', 'normal', 'high' );
	add_meta_box( 'wp-page-meta-boxes', 'WP-Player 播放器选项', 'wpPlayer_post_meta_boxes', 'page', 'normal', 'high' );
}

/**
 * Array of variables for post meta boxes.  Make the 
 * function filterable to add options through child themes.
 */
function wp_player_meta_boxes( $val = true, $apply = false ) {
	$arr1 = array(
		'mp3_xiami_type' => array(
			'name' => 'mp3_xiami_type',
			'type' => 'select',
			'options' => array(
				'song' => '单音乐页面',
				'album' => '专辑页面',
				'artist' => '艺人页面', 
				'collect' => '精选集页面'
			),
			'output' => false
		),
		'mp3_xiami' => array(
			'name' => 'mp3_xiami',
			'type' => 'text',
			'description' => '即可填音乐写地址ID，也可填写虾米音乐网址 http://......',
			'button' => '获取音乐ID',
			'output' => false
		),
	);
	
	$arr2 = array(
		'mp3_title' => array(
			'name' => 'mp3_title',
			'type' => 'text',
			'title' => '歌曲名',
			'description' => '请填写歌曲名',
			'output' => true
		),
		'mp3_author' => array(
			'name' => 'mp3_author',
			'type' => 'text',
			'title' => '歌手名',
			'description' => '请填写歌手名',
			'output' => true
		),
		'mp3_address' => array(
			'name' => 'mp3_address',
			'type' => 'upload',
			'title' => '歌曲地址',
			"description" => '在这里可以上传MP3文件，也可以自己填写MP3外链地址，默认请带上 http://',
			'output' => true
		),
		'mp3_thumb' => array(
			'name' => 'mp3_thumb',
			'type' => 'upload',
			'title' => '歌曲封面',
			"description" => '上传封面，可以为空，WP-Player 将使用默认图',
			'output' => true
		)
	);

	$meta_boxes = $val ? $arr1 : $arr2;

	if ( $apply ){
		$meta_boxes = array();
		$meta_boxes = array_merge( $arr1, $arr2 );
	}

	return apply_filters( 'wp_player_meta_boxes', $meta_boxes );
}


/**
 * get meta boxes 
 */
function get_wp_player_metaBox( $val = true ){
	global $post;
	$meta_boxes = wp_player_meta_boxes($val);
	foreach ( $meta_boxes as $meta ){
		$value = get_post_meta( $post->ID, $meta['name'], true );
		switch ($meta['type']) {
			case 'text': wp_player_get_meta_text_input( $meta, $value ); break;
			case 'select': wp_player_get_meta_select( $meta, $value ); break;
			case 'upload': wp_player_get_meta_upload( $meta, $value ); break;
			case 'button': wp_player_get_meta_button( $meta, $value ); break;
		}
	}
}

/**
 * Displays meta boxes on the Write Post panel.  Loops 
 * through each meta box in the $meta_boxes variable.
 * Gets array from solostream_post_meta_boxes().
 */

function wpPlayer_post_meta_boxes() {
	global $post; ?>
	<div class="wp-player-wrap" id="wp-player-wrap">
		<ul class="wp-player-tabs" id="wp-player-tabs">
			<li class="current"><a href="javascript: void(0);">虾米歌曲</a></li>
			<li><a href="javascript: void(0);">手动上传</a></li>
		</ul>
		<div class="wp-player-row" id="wp-player-row">
			<div class="wp-player-inner current">
				<p><strong>填写方法</strong></p>
				<ol>
					<li>在虾米网打开喜欢的歌曲页面，复制歌曲页面的网址如：<code>http://www.xiami.com/song/2078022......</code></li>
					<li>并将复制的网址填写到后面的表单内。音乐类型将根据网址自动做出选择。</li>
					<li>点击<code>获取音乐ID</code>按钮，此时音乐ID出现在表单中。</li>
					<li>将短代码 <code>[player autoplay="1"]</code> 填入您的文章内容中。</li>
					<li>短代码中 <code>autoplay</code> 表示是否自动播放；参数<code>"0"</code>表示否；<code>"1"</code>表示是；</li>
					<li>支持播放歌单：单音乐页面、专辑页面、艺人页面、精选集页面。</li>
					<li><code>PS：</code>建议使用网址来获取音乐ID。</li>
				</ol>
				<div class="wp-player-input"><?php get_wp_player_metaBox(); ?></div>
			</div>
			<div class="wp-player-inner"><?php get_wp_player_metaBox(false); ?></div>
		</div>
	</div>
<?php
}


/**
 * Outputs a text input box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */

function wp_player_get_meta_text_input( $args = array(), $value = false ) {

	extract( $args );

	$html .= $output ? '<div class="wp-player-input"><p>'.$title.'</p><p>' : "\n";
	$html .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.esc_html( $value ).'" class="wp-player-text" placeholder="'.$description.'" />';
	$html .= '<input type="hidden" name="'.$name.'_noncename" id="'.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
	if ( $button ){
		$html .= "\n".'<button id="wp_player_get_xiami_id" type="button" class="button wp-player-button">'.$button.'</button>';
	}
	$html .= $output ? '</p></div>'."\n" : "\n";

	echo $html;
}

/**
 * Outputs a select box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_select( $args = array(), $value = false ) {

	extract( $args );

	$html .= $output ? '<div class="wp-player-input"><p>'.$title.'</p><p>' : "\n";
	$html .= '<select class="wp-player-select" name="'.$name.'" id="'.$name.'">';
	foreach ( $options as $key => $option ){
		$selected = ($key == $value) ? 'selected="selected"' : '';
		$html .= '<option value="'.$key.'" '.$selected.'>'.$option.'</option>';
	}
	$html .= '<input type="hidden" name="'.$name.'_noncename" id="'.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
	$html .= $output ? '</p></div>'."\n" : "\n";

	echo $html;
}

/**
 * Outputs a Upload box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_upload( $args = array(), $value = false ) {

	extract( $args ); 

	$html .= $output ? '<div class="wp-player-input"><p>'.$title.'</p><p>' : "\n";
	$html .= '<input type="text" name="'.$name.'" id="'.$name.'" value="'.esc_html($value).'" class="wp-player-text" placeholder="'.$description.'" />'."\n";
	$html .= '<input id="'.$name.'_button" type="button" class="WP-Player-File button-secondary" value="点击上传" />';
	$html .= '<input type="hidden" name="'.$name.'_noncename" id='.$name.'_noncename" value="'.wp_create_nonce( plugin_basename( __FILE__ ) ).'" />';
	$html .= $output ? '</p></div>'."\n" : "\n";

	echo $html;
}

/**
 * Loops through each meta box's set of variables.
 * Saves them to the database as custom fields.
 */

function wp_player_save_meta_data( $post_id ) {
	global $post;

	$meta_boxes = wp_player_meta_boxes( true, true );

	foreach ( $meta_boxes as $meta_box ){
		
		if ( 'page' == $_POST['post_type'] && !current_user_can( 'edit_page', $post_id ) ){
			return $post_id; 
		}

		if ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) ){
			return $post_id;
		}

		if ( !wp_verify_nonce( $_POST[$meta_box['name'] . '_noncename'], plugin_basename( __FILE__ ) ) ){
		 	return $post_id;
		}

		$data = stripslashes( $_POST[$meta_box['name']] );

		if ( get_post_meta( $post_id, $meta_box['name'] ) == '' ){
			add_post_meta( $post_id, $meta_box['name'], $data, true );
		} elseif ( $data != get_post_meta( $post_id, $meta_box['name'], true ) ){
			update_post_meta( $post_id, $meta_box['name'], $data );
		}elseif ( $data == '' ){
			delete_post_meta( $post_id, $meta_box['name'], get_post_meta( $post_id, $meta_box['name'], true ) );
		}

	}

}
?>