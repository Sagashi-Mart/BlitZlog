<?php
$Error = false;
$Blog = [];
$Link = "";
$Flw = "";
$DATA = [];

if(file_exists("data/blog.db")) {
    if(class_exists("SQLite3")) {
        $db = new SQLite3("data/blog.db");
        $blogQuery = $db->query("SELECT * FROM setting");
        while($blogResult = $blogQuery->fetchArray()) {
            $Blog["theme"] = $blogResult[1];
            $Blog["name"] = $blogResult[2];
            $Blog["perPage"] = $blogResult[3];
            $Blog["mainteNance"] = $blogResult[4];
            $Blog["timezone"] = $blogResult[5];
            $Blog["search"] = $blogResult[6];
            $Blog["about"] = $blogResult[7];
        }
        $DATAQuery = $db->query("SELECT * FROM DATA");
        while($DATAResult = $DATAQuery->fetchArray()) {
            $DATA["version"] = $DATAResult[1];
            $DATA["copyright"] = $DATAResult[2];
            $DATA["key"] = $DATAResult["BLOG_KEY"];
        }
        $LinkQuery = $db->query("SELECT * FROM quicklink WHERE visibility = 1 ORDER BY position ASC");
        while($LinkResult = $LinkQuery->fetchArray()) {
            $Link .= <<<eof
            <li>
                <a href="article?id={$LinkResult["article"]}">{$LinkResult["name"]}</a>
            </li>
            eof;
        }
        $FlwQuery = $db->query("SELECT * FROM follow WHERE visibility = 1 ORDER BY position ASC");
        while($FlwResult = $FlwQuery->fetchArray()) {
            $Flw .= <<<eof
            <li>
                <a href="//{$FlwResult["link"]}" target="_blank"><i class="bi bi-{$FlwResult["icon"]}"></i> {$FlwResult["platform"]}</a>
            </li>
            eof;
        }
        date_default_timezone_set($Blog["timezone"]);
    } else {
        $Error = '<div class="alert alert-danger">SQLite3がご利用不可能な状態です。<br>
        php.iniにある、”;extension=sqlite3”の;を外してください。<br><br>
        それでもこのメッセージが表示された場合、無効になってるか、SQLite3のモジュールがインストールされていません。<br>
        インストール方法は<a href="https://www.php.net/manual/ja/sqlite3.installation.php">こちら</a>をご参照ください。</div>';
    }
} else {
    if(file_exists("data") && is_dir("data")) {
        $Error = '<div class="alert alert-danger">blog.dbファイルが存在しません。<br>
        回復用のblog.dbファイルが<a href="//sagashi0120.cloudfree.jp/okiba/blog/#dbFileDL">置き場</a>にありますのでそこからダウンロードしてお使いください。</div>';
    } else {
        $Error = '<div class="alert alert-danger">dataフォルダが存在しません。<br>
        dataフォルダを作成し、回復用のblog.dbファイルが<a href="//sagashi0120.cloudfree.jp/okiba/blog/#dbFileDL">置き場</a>にありますのでそこからダウンロードしてお使いください。</div>';
    }
}