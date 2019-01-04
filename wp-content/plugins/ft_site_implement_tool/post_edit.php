<?php
/**
 * 投稿画面に実装内容を表示する
 */
function disp_post_meta_box(){
	// 投稿IDから実装内容を取得してリストに変換
	$data = get_implement_data(get_the_ID());
	$convert_data = mb_convert_encoding($data, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$decode_data = json_decode($convert_data, true);
	
	$implemented = true;
	if(empty($decode_data['list'])){
		echo '<p class="howto">実装項目はありません</p>';
	}
	foreach($decode_data['list'] as $item){
		$implemented = '';
		if($item['status'] == 0) {
			$implemented = '<span style="margin:1em;"><input type="checkbox" name="implemented[]" value="'.$item['id'].'">実装済みにする</input></span>';
		}
		else {
			// 実装済みの場合、実装済みと表示
			$implemented = '<span style="font-weight: 600;color:red;margin:1em;">実装済み</span>';
		}
		if($item['implement_type'] == 1){
			echo '<h2>ページ統合'.$implemented.'</h2>';
			echo 'カスタム Canonical URLに以下を追加';
			echo '<textarea onclick="this.select();" style="width:100%">'.htmlspecialchars($item['implement_url']).'</textarea>';
		}
		if($item['implement_type'] == 2){
			echo '<h2>ページ削除'.$implemented.'</h2>';
			echo '<p>公開状態を非公開に設定する</p>';
		}
		if($item['implement_type'] == 3){
			echo '<h2>リンク追加'.$implemented.'</h2>';
			echo '<textarea onclick="this.select();" style="width:100%">'.htmlspecialchars($item['implement_value']).'</textarea>';
		}
		if($item['implement_type'] == 4){
			echo '<h2>リンク削除'.$implemented.'</h2>';
			echo '<textarea onclick="this.select();" style="width:100%">'.htmlspecialchars($item['implement_value']).'</textarea>';
		}
	}
}

/**
 * 投稿IDから施策対象データを取得する
 */
function get_implement_data($post_id){
	$data = array(
		'host' => HOST,
		'implement_type' => 0,
		'post_id' => $post_id,
	);
	$response = wp_remote_post(IMPLEMENT_LIST_URL, 
		array(
		'body'    => $data,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( BOXDOT_USER . ':' . BOXDOT_PASSWORD ),
		),
	) );
	$resbody = wp_remote_retrieve_body($response);
	// $rescode = wp_remote_retrieve_response_code($response);
	return $resbody;
}
/**
 * 実装済みにする
 */
function save_implement_status(){
	//投稿ID
	$post_id = get_the_ID();
	// 自動保存の時は呼ばない
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	//新規追加の時は呼ばない
	if(empty($post_id)){
		echo 'sinki'.$post_id.'sinki';
		return;
	}
	/* 権限チェック */
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	
	// 実装済みにするにチェックが入っていたらステータス更新
	$value = isset($_POST['implemented']) ? $_POST['implemented'] : array();
	if($value){
		foreach($value as $id){
			// それぞれのIDでAPIをたたく
			$data = array(
				'host' => HOST,
				'id' => $id,
				'status' => 1,
			);
			$response = wp_remote_post(IMPLEMENT_UPDATE_STATUS_URL, 
				array(
				'body'    => $data,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( BOXDOT_USER . ':' . BOXDOT_PASSWORD ),
				),
			) );
		}
	}
}
// WPの更新ボタンに実装済み更新処理をフックする
add_action('wp_insert_post', 'save_implement_status');
?>