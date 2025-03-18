<?php
$i = 0;
$ps = "./";
$SKIP = false;
$path = $_GET["path"] ?? "";
$segments = explode("/",$path);
if(!empty($_GET["path"])) {
        foreach($segments as $index => $name) {
                if(isset($segments[$i])) {
                        if(empty($segments[$i])) {
                                $ps .= "../";
                        } elseif($SKIP === false) {
                                $SKIP = true;
                        } else {
                                $ps .= "../";
                        }
                        ++$i;
                }
        }
}
$random = str_shuffle(1234567890);
$Head = <<<eof
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=IBM%20Plex%20Sans%20JP" />
        <link rel="stylesheet" href="{$ps}css/style.css?$random" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="{$ps}theme/{$Blog["theme"]}/style.css?$random" />
        <script src="{$ps}js/jquery-3.7.1.min.js"></script>
        <script src="{$ps}js/main.blitzlog.js?$random"></script>\n
eof;