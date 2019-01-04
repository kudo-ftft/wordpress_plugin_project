<?php
/**
 * 一覧ページを表示する
 */
function show_index_page() {
	global $wpdb;
	global $wp_query;
	$status_list = ["未","済"];
	$check_mark = ["checked","checked","checked","checked"];
	$finished_check_mark = ["checked","",""];
	$wp_n = wp_nonce_field('ft_text');
	$finished_display = 1;
	echo <<<EOS
	<style>
	table.menu{
		padding:1px;
		width: 60%!important;
	}
	table{
		border-collapse:collapse!important;
		table-layout:fixed!important;
	}
	table th.result{
		border-width: thin; 
		border-style: solid; 
		-moz-border-radius: 20%
		text-align:left!important;
		word-wrap:break-word!important;
	}
	table tr.result{
		border-width: thin; 
		border-style: solid; 
		-moz-border-radius: 20%
		word-wrap:break-word!important;
	}
	table td.result{
		border-collapse:collapse;
		border: solid 1px;
		word-wrap:break-word!important;
	}
	</style>
EOS;
	if(isset($_POST['check_implement_type_'])):
		$check_mark[0] = "";
		$check_mark[1] = "";
		$check_mark[2] = "";
		$check_mark[3] = "";
		foreach($_POST['check_implement_type_'] as $check):
			if($check === '1'):$check_mark[0] = "checked";
			elseif($check === '2'):$check_mark[1] = "checked";
			elseif($check === '3'):$check_mark[2] = "checked";
			elseif($check === '4'):$check_mark[3] = "checked";
			endif;	
		endforeach;
	endif;
	
	if(isset($_POST['finished_display_'])):
		$finished_check_mark[0] = "";
		$finished_check_mark[1] = "";
		$finished_check_mark[2] = "";
		foreach($_POST['finished_display_'] as $fin):
			if($fin === "1"):$finished_check_mark[0] = "checked";
			elseif($fin === "2"):$finished_check_mark[1] = "checked";
			elseif($fin === "3"):$finished_check_mark[2] = "checked";
			endif;
			$finished_display = $fin;
		endforeach;	
	endif;
	
	echo <<<EOS
	<div class="wrap">
		<h2>株式会社エフティー サイト実装情報 一覧</h2>
		<br>
		<table class="menu">
			<form method="post" action="">
				{$wp_n}
				<tr>
					<td>
						<input type="checkbox" name="check_implement_type_[]" value="1" id="check_implement_type_0" $check_mark[0] />
						<label for="check_implement_type_0" style="font-size:16px;">ページ統合</label>
					</td>
					<td>
						<input type="checkbox" name="check_implement_type_[]" value="2" id="check_implement_type_1" $check_mark[1] />
						<label for="check_implement_type_1" style="font-size:16px;">ページ削除</label>
					</td>
					<td>
						<input type="checkbox" name="check_implement_type_[]" value="3" id="check_implement_type_2" $check_mark[2] />
						<label for="check_implement_type_2" style="font-size:16px;">リンク追加</label>
					</td>
					<td>
						<input type="checkbox" name="check_implement_type_[]" value="4" id="check_implement_type_3" $check_mark[3] />
						<label for="check_implement_type_3" style="font-size:16px;">リンク削除</label>
					</td>
				</tr>
				<tr>
					<td style="padding:30px!important;">
						<input type="Submit"  name="Submit" class="button-primary" value="　一覧取得　" />
					</td>
					<td>
						<input type="radio"  name="finished_display_[]" value="1" id="finished_display_1" $finished_check_mark[0] />
						<label for="finished_display_1">「済」の非表示<br>&nbsp;</label>
					</td>
					<td>
						<input type="radio"  name="finished_display_[]" value="2" id="finished_display_2" $finished_check_mark[1] />
						<label for="finished_display_2">「済」を表示<br>（チェック機能付き）</label>
					</td>
					<td>
						<input type="radio"  name="finished_display_[]" value="3" id="finished_display_3" $finished_check_mark[2] />
						<label for="finished_display_3">「済」を表示<br>（チェック機能なし）</label>
					</td>					
				</tr>
			</form>
		</table>
EOS;
	if(isset($_POST['check_implement_type_']) && check_admin_referer('ft_text')){
		echo <<<EOS
		<table class='wp-list-table widefat fixed'>
			<tr bgcolor="#e6e6fa">
				<th class='result' style="width:5%;text-align:center!important;">投稿ID</th>
				<th class='result' style="width:30%;text-align:center!important;">作業対象ページ</th>
				<th class='result' style="width:6%;text-align:center!important;">実装の種類</th>
				<th class='result' style="width:30%;text-align:center!important;">タグに使用するページ</th>
				<th class='result' style="width:4%;text-align:center!important;">状況</th>
			</tr>
			<tbody>
EOS;
				foreach($_POST['check_implement_type_'] as $c):
					$data = get_implment_list(intval($c));
					$convert_data = mb_convert_encoding($data, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$decode_data = json_decode($convert_data,true);

					if(!isset($decode_data['list'])) continue;
					foreach($decode_data['list'] as $d):
						$tr_color = "";
						$post_ = "";
						$post_id = "";
						$url = "";
						$title = "";
						$implement_url = "";
						$implement_type = "";
						$implement_value = "";
						$edit_url = "";
						$status = "";
						$url_checkflag = false;
						$canonical_checkflag = false;
						$private_checkflag = false;
						if(isset($d['url']))$url = $d['url'];
						if(isset($d['implement_type']))$implement_type = IMPLEMENT_TYPE_NAME[intval($d['implement_type'])]; 
						if(isset($d['implement_url']))$implement_url = $d['implement_url'];
						if(isset($d['implement_value']))$implement_value = $d['implement_value'];
						if(isset($d['status']))$status = $status_list[intval($d['status'])];

						$post_id = url_to_postid($url);
						$post_ = get_post($post_id);
						$edit_url = get_edit_post_link_ft($url);
						$title = get_title_ft($url);
						$implement_title = get_title_ft($implement_url);
						//デフォルトでは済を表示しない
						if($finished_display === "1" && $status === "済")continue;
						
						//ステータスが済になっていればグレー
						if($finished_display === "2" || $finished_display === "3"):
							if($status === "済")$tr_color = BG_COLOR_GRAY;
						endif;
						
						//ページ統合の場合はcanonicalをチェックする
						if($c === "1" && $finished_display === "2"):
							$canonical_checkflag = canonical_check_ft($url,$implement_url);
							if($canonical_checkflag && $status === "済")$tr_color = BG_COLOR_GREEN;
						endif;
						
						//ページ削除の場合は非表示になっているかどうかをチェックする
						if($c === "2" && $finished_display === "2"):	
							$private_checkflag = private_check_ft($url);
							if($private_checkflag && $status === "済")$tr_color = BG_COLOR_GREEN;
						endif;
						
						//リンクの追加の場合は記事内のリンクをチェックする
						if($c === "3" && $finished_display === "2"):
							$url_checkflag = check_url_in_post_content_ft($post_,$implement_url); 
							if($url_checkflag && $status === "済")$tr_color = BG_COLOR_GREEN;
						endif;
						
						//リンクの削除の場合はリンクが無いことをチェックする
						if($c === "4" && $finished_display === "2"):
							$url_checkflag = check_url_in_post_content_ft($post_,$implement_url);
							if(!$url_checkflag && $status === "済")$tr_color = BG_COLOR_GREEN;
						endif;

						echo 
							"<tr class='result' $tr_color>
								<td class='result' style='vertical-align:middle;text-align:center!important;'>
									<a href=".$edit_url." target='_blank'>
									<input type='button' class='button-secondary' value=".$post_id."></a>
								</td>
								<td class='result'>".$title."<br><a href=".$url." target='_blank'>".urldecode ($url)."</a></td>
								<td class='result' style='vertical-align:middle;text-align:center!important;'>".$implement_type."</td>
								<td class='result'>".$implement_title."<br><a href=".$implement_url." target='_blank'>".urldecode($implement_url)."
								</a><!--<br><code>".htmlspecialchars($implement_value)."</code></td>-->
								<td class='result' style='vertical-align:middle;text-align:center!important;'><b>".$status."</b></td>
							</tr>";
					endforeach;
				endforeach;
			echo "</tbody>";
		echo "</table>";
	echo "</div>" ;
	}
}
/**
 * 実装情報リストを取得
 */
function get_implment_list($implement_type){
	//$response = remote_post_data($implement_type);
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
	$resbody = wp_remote_retrieve_body($response);
	$rescode = wp_remote_retrieve_response_code($response);
	return $resbody;
}

//記事編集管理画面へのURLを取得
function get_edit_post_link_ft($url){
	$post_id = url_to_postid($url);
	$post = get_post($post_id);
	$admin_post_url = get_edit_post_link($post);
	return $admin_post_url;
}
//URL（パーマリンク）を取得
function get_url_ft($url){
	$post_id = url_to_postid($url);
	$post = get_post($post_id);
	$url = get_permalink($post);
	return $url;
}
//記事タイトルを取得
function get_title_ft($url){
	$post_id = url_to_postid($url);
	$post = get_post($post_id); 
	$title = get_the_title($post);
	return $title;
}
//記事が非表示であるかどうか。※実装種別でいえばページ削除
function private_check_ft($url){
	$post_id = url_to_postid($url);
	if ( get_post_status ($post_id) === 'private' ) {
		return true;
	} else {
		return false;
	}
}

////wordpressでパーマリンクをデフォルト設定していると「例：https://ドメイン/?p=2」
//pathの部分がない為か正常に分解処理されない。※「/」だけが取得される状態となる。
//その際はURL全体を変数に格納   
function url_parse_impossible_ft($url){
	$url_data = "";
	$match_url = array();
	preg_match('/(https?:\/\/)(.*)(["|\'|\s]){0,}/',$url,$match_url);
	if(array_key_exists(2,$match_url)):
		if(isset($match_url[2])):
			$url_data = $match_url[2];
			return $url_data;
		endif;
	endif;
	return $url_data;
}
//URLのpath部分を抽出
//path部分を抽出出来ないイレギュラーなURL「例：https://ドメイン/?p=2」
//などのpath部分が無いURLに関してはurl_parse_impossible_ftで処理
function url_parse_ft($urldata){
	$original_url = $urldata;
	$convert_url = "";
	$urldata= parse_url($urldata);
	if(array_key_exists('path',$urldata)):
		if(isset($urldata['path'])):
			$convert_url = $urldata['path'];
			if($convert_url ==="/"):
				$convert_url = url_parse_impossible_ft($original_url);
			endif;
		endif;
	endif;
	return $convert_url;
}
//URLのhost部分を抽出
function url_parse_host_ft($urldata){
	$convert_url = "";
	$urldata= parse_url($urldata);
		if(array_key_exists('host',$urldata)):
			if(isset($urldata['host'])):
			$convert_url = $urldata['host'];
		endif;
	endif;
	return $convert_url;
}
//URLのquery部分を抽出※？以降
function url_parse_query_ft($urldata){
	$convert_url = "";
	$urldata= parse_url($urldata);
		if(array_key_exists('query',$urldata)):
			if(isset($urldata['query'])):
			$convert_url = $urldata['query'];
		endif;
	endif;
	return $convert_url;
}
/*
   記事本文内のリンクを探す
1、正規表現を使用してpreg_match
2、マッチした部分の内、path部分を比較。そもそもこの部分のマッチが前提
3、parse_url(host、query)部分がもしあれば比較。※相対パスなどhost部分が無ければスルー
*/
function url_check_ft($post,$url){
	$url_host = "";
	$url_path = "";
	$url_query = "";
	
	if(url_parse_host_ft($url) !== ""):
		$url_host = url_parse_host_ft($url);
		$url_host = preg_quote($url_host,'/');
	endif;
 
	if(url_parse_ft($url) !== ""):
		$url_path = url_parse_ft($url);
		$url_path = preg_quote($url_path,'/');
	endif;
	
	if(url_parse_query_ft($url) !== ""):
		$url_query = url_parse_query_ft($url);
		$url_query = preg_quote($url_query,'/'); 
	endif;
	
	$match_host = "";
	$match_path = "";
	$match_query = "";
	$targetUrl = "";
	$match = array();
	//do_short_codeで記事本文内にあるショートコードを実行した上で記事本文を取得
	if(isset($post->post_content)):
		$postData = do_shortcode(apply_filters('the_content',$post->post_content));
	else:
		return false;
	endif;
	
	//$matchFlag = preg_match('/<a.*?{0,}href=["\'|\s]{0,}(.*?){0,}('.$url_host.'){0,}('.$url_path.')('.$url_query.'){0,}["\'|\s]{0,}(.*?){0,}\//',$postData,$match);
	$matchFlag = preg_match('/<a(.*?)href=["\'|\s]{0,}(.*?){0,}('.$url_host.'){0,}('.$url_path.')('.$url_query.'){0,}["\'|\s]{0,}(.*?){0,}>/',$postData,$match);
	
	//マッチした内のhost部分を変数へ格納
	if(array_key_exists(3,$match)):
		if(isset($match[3])):
			$match_host = $match[3];
		endif;
	endif;
	//マッチした内のpath部分を変数へ格納
	if(array_key_exists(4,$match)):
		if(isset($match[4])):
			$match_path = $match[4];
		endif;
	endif;
	//マッチした内のquery部分を変数へ格納
	if(array_key_exists(5,$match)):
		if(isset($match[5])):
			$match_query = $match[5];
		endif;
	endif;
	//前提としてurlのpath部分が等しいかどうか
	if($match_path !== "" && strcmp($match_path,url_parse_ft($url)) == 0):
	//host部分があればチェック
		if($match_host !== ""):
			if(strcmp($match_host,url_parse_host_ft($url)) == 0):;
			else: 
				return false;
			endif;
		endif;
	//urlの末尾※query部分があればチェック
		if($match_query !== ""):
			if(strcmp($match_query,url_parse_query_ft($url)) == 0):;
			else:
				return false;
			endif;
		endif;
		return true;
	else :
		return false;
	endif;
}

/*canonicalのチェック
1、get_post_metaを使用してcanonicalのURLを取得
2、hrefが取得出来たら相対パスで設定されている場合も考慮してget_permalinkで正常なパーマリンクの状態にする
3、URLを比較してOKであればtrueを返す。
*/
function canonical_check_ft($url,$implement_url){
	$post_id = url_to_postid($url);
	//カスタムフィールドからcanonicalのURLを取得
	$cano_url = get_post_meta($post_id,'_aioseop_custom_link',true);
	if($cano_url === "")return false;
	//一応相対パスで設定されている場合も考慮してパーマリンクへ変換
	$cano_url = get_url_ft($cano_url);
    $implement_url = get_url_ft($implement_url);
	if(strcmp($cano_url,$implement_url) === 0):
		return true;
	endif;
	return false;
}
?>