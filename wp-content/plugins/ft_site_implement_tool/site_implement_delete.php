<?php
/**
 * 削除ページを表示する
 */
function show_delete_page() {
	$wp_n = wp_nonce_field('ft_implement_delete');
	
	echo <<<EOS
	<div class="wrap">
		<h2>株式会社エフティー サイト実装情報 削除</h2>
		<p style="margin:40px;">
			<div>
				<!--<h2>施策項目を削除</h2>-->
				<form id="deleteImplement" method="post" action="">
					{$wp_n}
					<select name="delete_implement_type">
					<option value="0">すべて</option>
					<option value="1">ページ統合(canonical)</option>
					<option value="2">ページ削除</option>
					<option value="3">リンク追加</option>
					<option value="4">リンク削除</option>
					</select>
					<input type="submit" name="deleteImplement" class="button-primary" value="施策項目を削除" />
				</form>
			</div>
		</p>
	</div>
EOS;
	//削除処理
	set_delete_implement_data();
}
/**
 * 削除用情報を作成
 */
function set_delete_implement_data(){
	if(!isset($_POST['delete_implement_type']) || !check_admin_referer('ft_implement_delete')){
		return;
	}
	delete_implement($_POST['delete_implement_type']);
}
/**
 * 実装内容削除を実行する
 */
function delete_implement($implement_type){
	$data = array(
		'host' => HOST,
		'implement_type' => $implement_type,
	);
	$response = wp_remote_post(IMPLEMENT_DELETE_URL, 
		array(
		'body'	=> $data,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( BOXDOT_USER . ':' . BOXDOT_PASSWORD ),
		),
	) );
	//$rescode = wp_remote_retrieve_response_code($response);
	return;
}
?>