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
	add_meta_box( 'wp-page-meta-boxes', 'WP-Player 播放器选项', 'wpPlayer_page_meta_boxes', 'page', 'normal', 'high' );
	
}

/**
 * Array of variables for post meta boxes.  Make the 
 * function filterable to add options through child themes.
 */
function wp_player_post_meta_boxes() {

	/* Array of the Post meta box options. */
	$meta_boxes = array(
		'wp_player_type' => array(
			'name' => 'wp_player_type',
			'type' => 'radio',
			'title' => '选择歌曲路线',
			'description' => '自动获取表示:获取歌曲的图片，作者，歌曲名，歌曲地址，手动则反之。',
			"options" => array(
				"自动获取",
				"手动上传"
			)
		),
		'mp3_xiami' => array(
			'name' => 'mp3_xiami',
			'type' => 'text',
			'title' => '虾米歌曲ID',
			"description" => '不知道怎样获取虾米歌曲ID，请点击<a href="http://webjyh.com/wp-player/" target="_blank">这里</a>'
		),
		'mp3_title' => array(
			'name' => 'mp3_title',
			'type' => 'text',
			'title' => '歌曲名'
		),
		'mp3_author' => array(
			'name' => 'mp3_author',
			'type' => 'text',
			'title' => '歌手'
		),
		'mp3_address' => array(
			'name' => 'mp3_address',
			'type' => 'upload',
			'title' => 'MP3外链',
			"description" => '在这里可以上传MP3文件，支持的媒体格式有mp3,mp4,m4a,ogg,oga，也可以自己填写MP3外链地址，默认请带上http://'
		),
		'mp3_thumb' => array(
			'name' => 'mp3_thumb',
			'type' => 'upload',
			'title' => '歌曲缩略图',
			"description" => '上传歌曲缩略图，可以为空，程序将使用默认图'
		)
	);

	return apply_filters( 'wp_player_post_meta_boxes', $meta_boxes );
}

/**
 * Array of variables for page meta boxes.  Make the 
 * function filterable to add options through child themes.
 */

function wp_player_page_meta_boxes() {

	/* Array of the Page meta box options. */
	$meta_boxes = array(
		'wp_player_type' => array(
			'name' => 'wp_player_type',
			'type' => 'radio',
			'title' => '选择歌曲路线',
			'description' => '自动获取表示:获取歌曲的图片，作者，歌曲名，歌曲地址，手动则反之。',
			"options" => array(
				"自动获取",
				"手动上传"
			)
		),
		'mp3_id' => array(
			'name' => 'mp3_id',
			'type' => 'text',
			'title' => '虾米歌曲ID',
			"description" => '不知道怎样获取虾米歌曲ID，请点击<a href="http://webjyh.com/wp-player/" target="_blank">这里</a>'
		),
		'mp3_title' => array(
			'name' => 'mp3_title',
			'type' => 'text',
			'title' => '歌曲名'
		),
		'mp3_author' => array(
			'name' => 'mp3_author',
			'type' => 'text',
			'title' => '歌手'
		),
		'mp3_address' => array(
			'name' => 'mp3_address',
			'type' => 'upload',
			'title' => 'MP3外链',
			"description" => '在这里可以上传MP3文件，支持的媒体格式有mp3,mp4,m4a,ogg,oga，也可以自己填写MP3外链地址，默认请带上http://'
		),
		'mp3_thumb' => array(
			'name' => 'mp3_thumb',
			'type' => 'upload',
			'title' => '歌曲缩略图',
			"description" => '上传歌曲缩略图，可以为空，程序将使用默认图'
		)
	);

	return apply_filters( 'wp_player_page_meta_boxes', $meta_boxes );
}

/**
 * Displays meta boxes on the Write Post panel.  Loops 
 * through each meta box in the $meta_boxes variable.
 * Gets array from solostream_post_meta_boxes().
 */

function wpPlayer_post_meta_boxes() {
	global $post;
	$meta_boxes = wp_player_post_meta_boxes(); ?>

	<table class="form-table" id="wp-player-tabs">
		<tr>
			<td>为什么会出现这个，这是因为你装了 WP-Player 插件而生成的，不知道如何使用，请点击<a href="http://webjyh.com/wp-player/" target="_blank">这里</a></td>
		</tr>
	<?php foreach ( $meta_boxes as $meta ) :

		$value = get_post_meta( $post->ID, $meta['name'], true );

		if ( $meta['type'] == 'text' )
			wp_player_get_meta_text_input( $meta, $value );
		elseif ( $meta['type'] == 'textarea' )
			wp_player_get_meta_textarea( $meta, $value );
		elseif ( $meta['type'] == 'radio' )
			wp_player_get_meta_radio( $meta, $value );
		elseif ( $meta['type'] == 'select' )
			wp_player_get_meta_select( $meta, $value );
		elseif ( $meta['type'] == 'upload' )
			wp_player_get_meta_upload( $meta, $value );
			
	endforeach; ?>
	</table>
<?php
}

/**
 * Displays meta boxes on the Write Page panel.  Loops 
 * through each meta box in the $meta_boxes variable.
 * Gets array from solostream_page_meta_boxes()
 */

function wpPlayer_page_meta_boxes() {
	global $post;
	$meta_boxes = wp_player_page_meta_boxes(); ?>

	<table class="form-table" id="wp-player-tabs">
		<tr>
			<td>为什么会出现这个，这是因为你装了 WP-Player插件而生成的，不知道如何使用，请点击<a href="http://webjyh.com/wp-player/" target="_blank">这里</a></td>
		</tr>
	<?php foreach ( $meta_boxes as $meta ) :

		$value = stripslashes( get_post_meta( $post->ID, $meta['name'], true ) );

		if ( $meta['type'] == 'text' )
			wp_player_get_meta_text_input( $meta, $value );
		elseif ( $meta['type'] == 'textarea' )
			wp_player_get_meta_textarea( $meta, $value );
		elseif ( $meta['type'] == 'radio' )
			wp_player_get_meta_radio( $meta, $value );
		elseif ( $meta['type'] == 'select' )
			wp_player_get_meta_select( $meta, $value );
		elseif ( $meta['type'] == 'upload' )
			wp_player_get_meta_upload( $meta, $value );
		
	endforeach; ?>
	</table>
<?php
}

/**
 * Outputs a text input box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */

function wp_player_get_meta_text_input( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<td>
			<label for="<?php echo $name; ?>" style="font-weight:bold;font-size:14px;"><?php echo $title; ?></label>
			<div style="margin-bottom:5px;font-size:12px;color:#666;display:block;"><?php echo $description; ?></div>
			<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo wp_specialchars( $value, 1 ); ?>" size="30" tabindex="30" style="width: 97%;" />
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

/**
 * Outputs a select box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_select( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<td>
			<label for="<?php echo $name; ?>" style="font-weight:bold;font-size:14px;"><?php echo $title; ?></label>
			<div style="margin-bottom:5px;font-size:12px;color:#666;display:block;"><?php echo $description; ?></div>
			<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
			<?php foreach ( $options as $option ) : ?>
				<option <?php if ( $value == $option ) echo ' selected="selected"'; ?>>
					<?php echo $option; ?>
				</option>
			<?php endforeach; ?>
			</select>
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

/**
 * Outputs a textarea with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */

function wp_player_get_meta_textarea( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<td>
			<label for="<?php echo $name; ?>" style="font-weight:bold;font-size:14px;"><?php echo $title; ?></label>
			<div style="margin-bottom:5px;font-size:12px;color:#666;display:block;"><?php echo $description; ?></div>
			<textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" cols="60" rows="4" tabindex="30" style="width: 97%;"><?php echo wp_specialchars( $value, 1 ); ?></textarea>
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

function wp_player_get_meta_radio( $args = array(), $value = false ) {
	
	extract( $args ); ?>
	
	<tr>
		<td>
			<p style="font-weight:bold;font-size:14px;"><?php echo $title; ?></p>
			<div style="padding:0px 0px 5px;font-size:12px;color:#666;display:block;"><?php echo $description; ?></div>
			<?php $i=0; foreach ( $options as $option ) : ?>
				<label><?php echo $option; ?><input type="radio" value="<?php echo $i; ?>" name="<?php echo $name; ?>" <?php if ( $i == $value ) echo ' checked="checked"'; ?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php $i++; endforeach; ?>
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}


/**
 * Outputs a Upload box with arguments from the 
 * parameters.  Used for both the post/page meta boxes.
 */
function wp_player_get_meta_upload( $args = array(), $value = false ) {

	extract( $args ); ?>

	<tr>
		<td>
			<label for="<?php echo $name; ?>" style="font-weight:bold;font-size:14px;"><?php echo $title; ?></label>
			<div style="margin-bottom:5px;font-size:12px;color:#666;display:block;"><?php echo $description; ?></div>
			<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo wp_specialchars( $value, 1 ); ?>" size="30" tabindex="30" style="width: 85%;" />
			<input id="<?php echo $name; ?>_button" type="button" class="WP-Player-File button-secondary" value="点击上传" />
			<input type="hidden" name="<?php echo $name; ?>_noncename" id="<?php echo $name; ?>_noncename" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</td>
	</tr>
	<?php
}

/**
 * Loops through each meta box's set of variables.
 * Saves them to the database as custom fields.
 */

function wp_player_save_meta_data( $post_id ) {
	global $post;

	if ( 'page' == $_POST['post_type'] ){
		$meta_boxes = array_merge( wp_player_page_meta_boxes() );
	} else {
		$meta_boxes = array_merge( wp_player_post_meta_boxes() );
	}
	
	foreach ( $meta_boxes as $meta_box ) :

		if ( !wp_verify_nonce( $_POST[$meta_box['name'] . '_noncename'], plugin_basename( __FILE__ ) ) )
			return $post_id;

		if ( 'page' == $_POST['post_type'] && !current_user_can( 'edit_page', $post_id ) )
			return $post_id;

		elseif ( 'post' == $_POST['post_type'] && !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$data = stripslashes( $_POST[$meta_box['name']] );

		if ( get_post_meta( $post_id, $meta_box['name'] ) == '' )
			add_post_meta( $post_id, $meta_box['name'], $data, true );

		elseif ( $data != get_post_meta( $post_id, $meta_box['name'], true ) )
			update_post_meta( $post_id, $meta_box['name'], $data );

		elseif ( $data == '' )
			delete_post_meta( $post_id, $meta_box['name'], get_post_meta( $post_id, $meta_box['name'], true ) );

	endforeach;
}
?>