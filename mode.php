<?php
/*
BlitZlog mode v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
require("src/head.php");
require("src/function.php");

$path = $_GET["path"] ?? "";
$segments = explode("/",$path);
$mode = $segments[0] ?? false;
$id = $segments[1] ?? false;
$return = [];
$modules = [];
$customPage = [];
$register_mode = [];

function errorPage() {
	global $return,$ps;
	$return["title"] = "404 Not Found";
	$return["main"] = <<<eof
	<h1>404 Not Found</h1>
	<p>お探しのページは見つかりませんでした。<br>URLを間違えているか、ページが削除された可能性があります。</p>
	<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
	eof;
}
$moduleBase = "module";
foreach(glob("$moduleBase/*",GLOB_ONLYDIR) as $file) {
	$module = basename($file);
	if($module == "__block" || $module == "__setting") continue;
	include_once("$moduleBase/$module/$module.php");

	$Head .= $modules[$module]["code"] ?? null;
    if(isset($modules[$module]["page"])) {
        if(isset($customPage[$module])) {
            $colliding_module_name = $register_mode[$module] ?? "Unknown";
            error_log("BlitZlog Warning: Custom URL page '{$module}' in module '{$module}' conflicts with existing module '{$colliding_module_name}'. The latter module ('{$module}')'s page will override.");
        }
        if(is_callable($modules[$module]["page"])) {
            $customPage[$module] = $modules[$module]["page"];
            $register_mode[$module] = $module;
        } else {
            error_log("BlitZlog Warning: Custom URL page '{$module}' in module '{$module}' is not a callable function.");
        }
    }
}
if(isset($customPage[$mode]) && is_callable($customPage[$mode])) {
	$id = htmlspecialchars($id);
	$return = $customPage[$mode]($id);
	$pankuzu = "<nav aria-label=\"breadcrumb\">
		<ul class=\"breadcrumb\">
			<li><a href=\"{$ps}\"><i class=\"bi bi-house\"></i> ホーム</a> <i class=\"bi bi-caret-right-fill\"></i></li>";
	$pankuzuList = explode(" > ",$return["pankuzu"]);
	$count = count($pankuzuList);
	$i = 0;
	foreach($pankuzuList as $value) {
		$i++;
		$value = htmlspecialchars($value);
		if($i === $count) {
			$pankuzu .= "<li>{$value}</li>";
		} else if($i === 1) {
			$pankuzu .= "<li><a href=\"{$ps}{$mode}\">{$value}</a> <i class=\"bi bi-caret-right-fill\"></i></li>";
		} else {
			$pankuzu .= "<li><a href=\"{$ps}{$mode}/{$id}\">{$value}</a> <i class=\"bi bi-caret-right-fill\"></i></li>";
		}
	}
	$pankuzu .= "</ul></nav>";
	$return["main"] = $pankuzu.$return["main"];
} else {
	$id = isset($_GET["q"]) && $mode == "search" ? $_GET["q"] : $id;
	$return = main($mode,$id);
	if($mode == "rss" && $return["title"] == "RSS 2.0 Page") {
		echo $return["main"];
		exit;
	} elseif($return["main"] === null && $return["title"] === null) {
		header("$protocol 404 Not Found");
		errorPage();
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?=$Blog["cms_name"]?> - <?=htmlspecialchars($return["title"],ENT_QUOTES,"UTF-8")?></title>
		<?=$Head?>
	</head>
	<body>
		<?php blogHeader(); ?>
		<div class="content">
			<?=$return["main"]?>
		</div>
		<?php blogFooter(); ?>
	</body>
</html>