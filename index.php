<?php
/*
BlitZlog v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
require("src/head.php");
require("src/function.php");
$return = main("top",$_GET["page"] ?? 1);

foreach(glob("module/*",GLOB_ONLYDIR) as $file) {
	$module = basename($file);
	if($module == "__block" || $module == "__setting") continue;
	include_once("module/$module/$module.php");

	$Head .= $modules[$module]["code"] ?? null;
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