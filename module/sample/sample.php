<?php
/*
BlitZlog module v0.4.0-beta
Sample Module
(c) 2025 yu-.,Sagashi Mart.
*/

$moduleName = "sample";
// 概要
$modules[$moduleName]["about"] = [
    "name" => "Sample Module",
    "img" => null,
    "category" => "Utility",
    "author" => "BlitZlog",
    "version" => "0.0.0",
    "description" => "This is a sample module for BlitZlog,demonstrating how to create a module with basic functionality.",
    "website" => "https://example.com/sample-module",
    "API_LEVEL" => 1,
];
// コード
$modules[$moduleName]["code"] = <<<eof
    <script>
        console.log("Sample Module Loaded");
    </script>
eof;
// 管理画面コード
$modules[$moduleName]["admin_code"] = <<<eof
    <script>
        console.log("Sample Module Admin Code Loaded");
    </script>
eof;
// カスタムページ
$modules[$moduleName]["page"] = function($id) {
    return [
        "title" => "Sample Page",
        "main" => "<h1>Sample Page</h1><p>This is a sample page for the Sample Module with ID: $id</p>",
        "pankuzu" => "Sample Module > Sample Page",
    ];
};
// 管理画面ページ
$modules[$moduleName]["admin_page"] = function($id) {
    return [
        "title" => "Sample Admin Page",
        "main" => "<h1>Sample Admin Page</h1><p>This is a sample page for the Sample Admin Module with ID: $id</p>",
        "pankuzu" => "Sample Admin Module > Sample Admin Page",
    ];
};
// 設定
$modules[$moduleName]["settings"] = [
    "sample_setting" => [
        "type" => "text",
        "label" => "Sample Setting",
        "description" => "This is a sample setting for the Sample Module.",
        "default" => "Default Value",
    ],
];