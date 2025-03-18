<?php
require("php/sqlite.php");
require("php/head.php");
require("php/function.php");

$path = $_GET["path"] ?? "";
$segments = explode("/",$path);
$mode = isset($segments[0]) ? $segments[0] : false;
$id = isset($segments[1]) ? $segments[1] : false;
if($mode === "search") {
    if(str_contains($_SERVER["REQUEST_URI"],"?q=")) {
        $q = urlencode($_GET["q"]);
        header("Location: ./$q");
    }
    $title = "検索「{$id}」";
} elseif($mode === "category") {
    $title = "カテゴリ「{$id}」";
} elseif($mode === "article") {
    $title = "記事「{$id}」";
} elseif($mode === "rss") {
    article("rss",1);
    exit;
} else {
    header("HTTP/1.1 404 Not Found");
    $title = "404 Not Found";
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$Blog["name"]?> - <?=$title?></title>
        <?=$Head?>
    </head>
    <body>
        <?php blogHeader(); ?>
        <?php
        if($mode === "search") {
            article("search",$id);
        } elseif($mode === "category") {
            article("category",$id);
        } elseif($mode === "article") {
            article("article",$id);
        } else {
            echo <<<eof
            <div class="content">
                <h1>404 Not Found</h1>
                <p>アクセスしたページは見つかりませんでした。<br>記事がまだ作成されていないか、URLを間違えた可能性があります。</p>
                <p><a href="./" rel="home">ホームへ戻る</a></p>
            </div>
            eof;
        }
        ?>
        <?php blogFooter(); ?>
    </body>
</html>