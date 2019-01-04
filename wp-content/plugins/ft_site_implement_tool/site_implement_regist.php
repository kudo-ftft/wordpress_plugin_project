<?php 
/**
 * 登録ページを表示する
 */
function show_regist_page() {
	global $wpdb;
	$text_area_1 = "";
	$text_area_2 = "";
	$text_area_3 = "";
	$text_area_4 = "";		
	$wp_nonce = wp_nonce_field('ft_implement_regist');	
	echo <<<EOS
		<style>
			textarea{
				width: 100%;
				height: 8em;
			}
			table{
				border-collapse:collapse!important;
				width:100%!important;
				table-layout:fixed!important;
			}
			table.form-table tr td{
				padding:1px;
				font-size:14px;
				marigin-top:20px;
			}
		</style>
EOS;
	if(isset($_POST['data_1']) && check_admin_referer('ft_implement_regist')):
		$text_area_1 =$_POST['data_1'];
		$text_area_2 =$_POST['data_2'];
		$text_area_3 =$_POST['data_3'];
		$text_area_4 =$_POST['data_4'];		 
	endif;
	echo <<<EOS
		<div class="wrap">
			<h2>株式会社エフティー サイト実装情報 登録</h2>
			<br>
			<form method="post" action="">
				{$wp_nonce}
				<table class="form-table">
					<tr valign="top">
						<td>ページ統合(canonical)</td>
					</tr>
					<tr valign="top">
						<td scope="row">
							<textarea name="data_1" id = "area1" />$text_area_1</textarea>
						</td>
					</tr>
					<tr valign="top">
						<td>ページ削除</td>
					</tr>
					<tr valign="top">
						<td scope="row">
							<textarea name="data_2" id = "area2" />$text_area_2</textarea>
						</td>
					</tr>
					<tr valign="top">
						<td>リンク追加</td>
					</tr>
					<tr valign="top">
						<td scope="row">
							<textarea name="data_3" id = "area3" />$text_area_3</textarea>
						</td>
					</tr>
					<tr valign="top">
						<td>リンク削除</td>
					</tr>
					<tr valign="top">
						<td scope="row">
							<textarea name="data_4" id = "area4" />$text_area_4</textarea>
						</td>
					</tr>
				</table>
				<p style="text-align:right;">
					<input type="button" id='clear_button' value="クリア" onclick="area1.value= '';area2.value = '';area3.value = '';area4.value = ''"/>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="登録" />
				</p>
			</form>
		</div>
EOS;
	//登録処理
	set_implement_data(1, 'data_1');
	set_implement_data(2, 'data_2');
	set_implement_data(3, 'data_3');
	set_implement_data(4, 'data_4');
}
/**
 * 登録する実装情報を作成
 */
function set_implement_data($implement_type, $text_name){
	if(empty($_POST[$text_name]) || !check_admin_referer('ft_implement_regist')){
		return;
	}
	//行ごとに配列化
	$row_data = explode("\n", $_POST[$text_name]);
	if(count($row_data) === 0){
		exit('<b style="font-size:20px;">登録するデータがありません</b>');
	}
	//行ごとにループ
	$rows = [];
	$count = 0;
	foreach($row_data as $row){
		//セル単位で抽出
		$cell_data = tab_convert_ft($row);
		$implement_post_id = "";
		$implement_url = "";
		$implement_title = "";
		$pv = 0;
		$link_add_flg = 0;
		$link_exist = 0;
		
		//作業ページURL
		$url = $cell_data[2];
		//作業ページの投稿ID
		$post_id = url_to_postid($url);
		
		//ページ統合
		if($implement_type == 1){
			//統合先ページのURL
			$implement_url = $cell_data[7];
			//統合先ページの投稿ID
			$implement_post_id = url_to_postid($implement_url);
		}
		//リンク追加
		elseif($implement_type == 3){
			//リンク先ページのタイトル
			$implement_title = $cell_data[6];
			//リンク先ページのURL
			$implement_url = $cell_data[7];
			//リンク先ページの投稿ID
			$implement_post_id = url_to_postid($implement_url);
		}
		//リンク削除
		elseif($implement_type == 4){
			//リンク先ページのタイトル
			$implement_title = $cell_data[6];
			//リンク先ページのURL
			$implement_url = $cell_data[7];
			//リンク先ページの投稿ID
			$implement_post_id = url_to_postid($implement_url);
			//作業ページのPV
			$pv = $cell_data[3];
			//作業ページが「リンク追加」で使用済みであるか
			$link_add_flg = $cell_data[8];
			//作業ページに、対象ページへのリンクが存在するか
			//（1:ある→リンク削除、0:ない→リンク追加）
			$post = get_post($post_id);
			if(check_url_in_post_content_ft($post, $implement_url)){
				$link_exist = 1;
			}
		}
		//APIに渡すため配列に追加
		$rows[] = [
			'post_id' => $post_id,
			'url' => $url,
			'implement_post_id' => $implement_post_id,
			'implement_url' => $implement_url,
			'implement_title' => $implement_title,
			'pv' => $pv,
			'link_add_flg' => $link_add_flg,
			'link_exist' => $link_exist
		];
		$count++;
	}
	insert_implement_data($implement_type, $rows);
}
/**
 * 実装情報を登録
 */
function insert_implement_data($implement_type, $rows){
	//$response = remote_post_data($implement_type);
	$data = [
		'host' => HOST,
		'implement_type' => $implement_type,
		'rows' => $rows
	];
	$response = wp_remote_post(IMPLEMENT_REGIST_URL, [
		'body' => $data,
		'headers' => [
			'Authorization' => 'Basic ' . base64_encode( BOXDOT_USER . ':' . BOXDOT_PASSWORD ),
		],
	]);
	if(is_wp_error($response)){
		$error_message = $response->get_error_message();
		echo $error_message;
	}
	return $response;
}

//タブで区切り⇒空白セル削除⇒余計なスペースを削除
function tab_convert_ft($text){
	$list = explode("\t", $text);		  //タブ区切り
	$list = array_map('trim', $list);	  // trim()をかける
	$list = array_values($list);		   // これはキーを連番に振りなおしてるだけ
	return $list;
}
?>