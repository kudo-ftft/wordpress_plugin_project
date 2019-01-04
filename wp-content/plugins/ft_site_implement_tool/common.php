<?php 
//API認証用ユーザ
define("BOXDOT_USER", "admin");
//API認証用パスワード（APIキーを更新した場合、変更が必要）
define("BOXDOT_PASSWORD", "eb8eb8472c7f3f98fbe020d6208e60e6fcdfb336938818c38a6c8f0fcedaaad7");
//ホスト名
define("HOST", $_SERVER['HTTP_HOST']);
//APIのURL
define("IMPLEMENT_LIST_URL", "http://brain.boxdot.jp/api/ImplementList");
define("IMPLEMENT_REGIST_URL", "http://brain.boxdot.jp/api/ImplementRegist");
define("IMPLEMENT_DELETE_URL", "http://brain.boxdot.jp/api/ImplementDelete");
define("IMPLEMENT_UPDATE_STATUS_URL", "http://brain.boxdot.jp/api/ImplementUpdateStatus");
//実装種別名
define("IMPLEMENT_TYPE_NAME", ["","ページ統合","ページ削除","リンク追加","リンク削除"]);
//一覧用背景色（ステータスによって変化する色）
define("BG_COLOR_GREEN","bgcolor='#90EE90'");
define("BG_COLOR_GRAY","bgcolor='#808080'");

/*
   記事本文内にリンクが存在するかチェック（true：存在する、false：存在しない）
*/
function check_url_in_post_content_ft($post, $url){
	//記事本文を取得（do_short_codeで記事本文内にあるショートコードを実行した上で取得）
	if(isset($post->post_content)):
		$post_content = do_shortcode(apply_filters('the_content',$post->post_content));
	else:
		return false;
	endif;
	//URLのscheme部分
	$url_scheme = parse_url($url, PHP_URL_SCHEME);
	//URLのHost部分
	$url_host = parse_url($url, PHP_URL_HOST);
	$url_host = preg_quote($url_host, '/');
	//URLのPath部分(先頭の/は除く)
	$url_path = parse_url($url, PHP_URL_PATH);
	$url_path = substr($url_path, 1);
	$url_path = preg_quote($url_path, '/');
	//URLのQuery部分(先頭に?を付加)
	$url_query = parse_url($url, PHP_URL_QUERY);
	$url_query = '?'.$url_query; 
	$url_query = preg_quote($url_query, '/');
	//aタグのhref属性に対象URLが設定されているか検索するパターン
	$pattern = '/<a(.*?)href[\s]*=[\s]*["\']?[\s]*(';
	if(!empty($url_scheme)){
		$pattern .= $url_scheme.':\/\/';
	}
	$pattern .= $url_host.'.*\/)?('.$url_path.')('.$url_query.')[\s]*["\']?[\s]*>/';
	//デバッグ用
	//echo '<pre>';
	//var_dump($url);
	//var_dump($url_scheme);
	//var_dump($url_host);
	//var_dump($url_path);
	//var_dump($url_query);
	//var_dump(htmlspecialchars($pattern));
	//echo '</pre>';
	if(preg_match($pattern, $post_content, $matches)){
		//echo '<pre>';
		//var_dump($matches);
		//echo '</pre>';
		//var_dump('存在する');
		return true;
	}
}

//old
function bak_remote_post_data($implement_type){
	//パラメータ
	$data = array(
		'host' => HOST,
		'implement_type' => $implement_type,
	);
	$response = wp_remote_post(IMPLEMENT_LIST_URL, 
		array(
		'body'	=> $data,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( BOXDOT_USER . ':' . BOXDOT_PASSWORD ),
		),
	) );
	echo $response;
	$resbody = wp_remote_retrieve_body($response);
	//$rescode = wp_remote_retrieve_response_code($response);
	////echo $rescode;
	return $resbody;
}
?>