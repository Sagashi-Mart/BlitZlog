<?php
/*
BlitZlog sqlite v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$Blog = [];
$Link = "";
$Flw = "";
$Head = "";
$DATA = [];

if(file_exists("{$documentRoot}{$ps}data/SYSTEM")) {
	if(class_exists("SQLite3")) {
		$db = new SQLite3("{$documentRoot}{$ps}data/SYSTEM");
		$blogQuery = $db->query("SELECT * FROM setting");
		foreach($blogQuery->fetchArray(SQLITE3_ASSOC) as $key => $value) {
			$Blog[$key] = isset($value) ? htmlspecialchars($value,ENT_QUOTES,"UTF-8") : null;
		}
		$DATAQuery = $db->query("SELECT * FROM DATA");
		foreach($DATAQuery->fetchArray(SQLITE3_ASSOC) as $key => $value) {
			$DATA[$key] = isset($value) ? htmlspecialchars($value,ENT_QUOTES,"UTF-8") : null;
		}
		$LinkQuery = $db->query("SELECT * FROM quicklink WHERE visibility = 1 ORDER BY position ASC");
		while($LinkResult = $LinkQuery->fetchArray(SQLITE3_ASSOC)) {
			$linkName = htmlspecialchars($LinkResult["name"]);
			$Link .= <<<eof
			<li>
				<a href="article?id={$LinkResult["article"]}">$linkName</a>
			</li>
			eof;
		}
		$FlwQuery = $db->query("SELECT * FROM follow WHERE visibility = 1 ORDER BY position ASC");
		while($FlwResult = $FlwQuery->fetchArray(SQLITE3_ASSOC)) {
			$FlwName = htmlspecialchars($FlwResult["platform"]);
			$Flw .= <<<eof
			<li>
				<a href="//{$FlwResult["link"]}" target="_blank"><i class="bi bi-{$FlwResult["icon"]}"></i> $FlwName</a>
			</li>
			eof;
		}
		date_default_timezone_set($Blog["timezone"]);
		$Head = <<<eof
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins&display=swap" />
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=IBM Plex Sans JP&display=swap" />
			<link rel="stylesheet" href="{$ps}css/style.css?$random" />
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
			<link rel="stylesheet" href="{$ps}theme/{$Blog["theme"]}/style.css?$random" />
			<script src="{$ps}js/jquery-3.7.1.min.js"></script>
			<script src="{$ps}js/main.blitzlog.js?$random"></script>
			
			<meta name="description" content="{$Blog["about"]}">\n
		eof;
	} else {
		$Error = '<p>SQLite3がご利用不可能な状態です。<br>
		php.iniにある、”;extension=sqlite3”の;を外してください。<br><br>
		それでもこのメッセージが表示された場合、無効になってるか、SQLite3のモジュールがインストールされていません。<br>
		インストール方法は<a href="https://www.php.net/manual/ja/sqlite3.installation.php">こちら</a>をご参照ください。</p>';
	}
} else {
	// 作り直し
}
if(isset($Error)) exit($Error);