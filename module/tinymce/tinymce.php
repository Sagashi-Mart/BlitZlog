<?php
/*
BlitZlog module v0.4.0-beta
TinyMCE Editor

(c) 2022-2025 Sagashi Mart. All Rights Reserved.
TinyMCE® and Tiny® are registered trademarks of Tiny Technologies, Inc.
*/
include_once("$documentRoot{$ps}theme/{$Blog["theme"]}/themes.php");
$editor_color = $themes[$Blog["theme"]]["about"]["color"] == "dark" ? "oxide-dark" : "oxide";
$editor_color2 = $themes[$Blog["theme"]]["about"]["color"] == "dark" ? "dark" : "default";

$moduleName = "tinymce";
$modules[$moduleName]["about"] = [
    "name" => "TinyMCE エディター",
    "category" => "Editor",
    "author" => "BlitZlog",
    "version" => "8.0.1",
    "description" => "BlitZlog用のTinyMCE エディター",
    "website" => "https://tiny.cloud/",
    "API_LEVEL" => 1,
];
$modules[$moduleName]["admin_code"] = <<<eof
    <script src="{$ps}module/tinymce/tinymce.min.js?r=8.0.1"></script>
    <script>
    function tinymceInit() {
        tinymce.init({
            selector: "textarea",
            language: "{$Blog["language"]}",
            height: 500,
            license_key: "gpl",
            skin_url: "{$ps}module/tinymce/skins/ui/{$editor_color}",
            content_css: "{$ps}module/tinymce/skins/content/{$editor_color2}/content.min.css",
            icons_url: "{$ps}module/tinymce/icons/default/icons.min.js",
            plugins: "accordion advlist anchor autolink autosave charmap code directionality emoticons fullscreen help image insertdatetime link lists media nonbreaking pagebreak preview save searchreplace table visualblocks visualchars wordcount",
            toolbar: "undo redo | styles fontsize | align bullist numlist | bold italic underline strikethrough | forecolor backcolor | link image media",
        });
    }
    </script>
eof;
$modules[$moduleName]["page"] = function($id) {
    $plugin = "";
    foreach(scandir("{$ps}module/tinymce/plugins") as $plugins) {
        if($plugins == "." || $plugins == "..") continue;
        $plugin .= "$plugins ";
    }
    return [
        "title" => "tinymce plugin",
        "main" => "$plugin",
        "pankuzu" => "tinymce",
    ];
};
$modules[$moduleName]["settings"] = [
    "tinymce_plugins" => [
        "type" => "checkbox",
        "label" => "有効にするプラグイン",
        "description" => "TinyMCEで有効にするプラグインを選択します。",
        "option" => [
            "advlist" => "リスト",
            "autolink" => "自動リンク",
            "image" => "画像挿入",
            "table" => "テーブル",
            "codesample" => "コードサンプル",
        ],
        "default" => "accordion advlist anchor autolink autosave charmap code codesample directionality emoticons fullscreen help image importcss insertdatetime link lists media nonbreaking pagebreak preview save searchreplace table visualblocks visualchars wordcount",
    ],
    "tinymce_toolbar" => [
        "type" => "text",
        "label" => "ツールバー",
        "description" => "ツールバーに表示するボタンをカンマ区切りで指定します。",
        "default" => "undo redo | styles fontfamily fontsize align | bold italic underline | forecolor backcolor | link image media",
    ],
];