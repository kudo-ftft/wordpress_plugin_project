<?php
/*
Plugin Name:  01株式会社エフティーサイト実装ツール
Plugin URI: 
Description:  株式会社エフティー サイト実装ツール
Author: FT
Version: 0.1
Author URI: 
*/

//共通処理
include('common.php');
//登録画面ソース読込
include('site_implement_regist.php');
//一覧画面ソース読込
include('site_implement_index.php');
//削除画面ソース読込
include('site_implement_delete.php');
//投稿編集画面用ソース読込
include('post_edit.php');

//メニュー、サブメニューを追加
add_action('admin_menu', 'admin_menu_action');
function admin_menu_action(){
	add_menu_page('株式会社エフティーサイト実装ツール', 'エフティー'.'<br>'.'サイト実装ツール', 'manage_options', 'ft_implement_tool_menu', '', '', 95);
	add_submenu_page('ft_implement_tool_menu', 'エフティーサイト実装ツール 一覧', '一覧', 'manage_options', 'ft_implement_index', 'show_index_page');
	add_submenu_page('ft_implement_tool_menu', 'エフティーサイト実装ツール 登録', '登録', 'manage_options', 'ft_implement_regist', 'show_regist_page');
	add_submenu_page('ft_implement_tool_menu', 'エフティーサイト実装ツール 削除', '削除', 'manage_options', 'ft_implement_delete', 'show_delete_page');
}
//サブメニューの非表示設定
add_action('admin_menu', 'hide_menu');
function hide_menu () {
    global $submenu;
    unset($submenu['ft_implement_tool_menu'][0]);
}

//投稿画面にメタボックスを追加
add_action('add_meta_boxes', 'show_meta_box');
function show_meta_box(){
	add_meta_box(
		'implement_meta_box', 
		'エフティー実装ツール', 
		'disp_post_meta_box', 
		'post', 
		'side', 
		'high'
	);
	add_meta_box(
		'implement_meta_box', 
		'エフティー実装ツール', 
		'disp_post_meta_box', 
		'page', 
		'side', 
		'high'
	);
}
?>