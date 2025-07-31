<?php
/*
BlitZlog admin v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$admin = true;
require("../src/head.php");
require("../src/function.php");

$path = $_GET["path"] ?? "home";
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$Blog["name"]?> 管理画面</title>
        <?=$Head?>
        <script>const BLOG_PATH = "<?=$path?>";const BLOG_TITLE = "<?=$Blog["name"]?> 管理画面";const PS = "<?=$ps?>";</script>
        <script src="<?=$ps?>js/admin.blitzlog.js?<?=$random?>"></script>
        <?php
        foreach(glob("../module/*",GLOB_ONLYDIR) as $file) {
            $module = basename($file);
            if($module == "__block") continue;
            include_once("../module/$module/$module.php");

            echo $modules[$module]["admin_code"] ?? null;
        }
        ?>
    </head>
    <body>
        <?php blogHeader(); ?>
        <div class="content"></div>

        <label for="popupFlag1">Popup 1</label>
        <input type="checkbox" class="popup-flag" id="popupFlag1">
        <label class="popup-background" for="popupFlag1"></label>
        <div class="popup">
            <label class="close-button" for="popupFlag1">×</label>
            <div id="popup-content">
            </div>
        </div>
        <?php blogFooter(); ?>
    </body>
</html>