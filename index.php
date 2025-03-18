<?php
require("php/sqlite.php");
require("php/head.php");
require("php/function.php");
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$Blog["name"]?> - Top</title>
        <?=$Head?>
    </head>
    <body>
        <?php blogHeader(); ?>
        <?php article("top",$_GET["page"] ?? 1); ?>
        <?php blogFooter(); ?>
    </body>
</html>