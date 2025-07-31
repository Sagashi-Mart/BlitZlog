<?php
/*
BlitZlog api[module] v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$api = true;
$admin = true;
$Folder = "../../module";
require("../../src/head.php");
require("../../src/sqlite.php");
require("../../src/login_check.php");

header("Content-Type: application/json");
if(!$adminLogin) {
    $responce_code = 401;
    echo json_encode([
        "code" => 401,
        "message" => "You are not login to the BlitZlog admin page."
    ],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    exit;
}

if(isset($_POST["install"])) {
    $type = $_POST["type"];
    switch($type) {
        case 0:
            StoreInstall($_POST["store-id"]);
            break;
        case 1:
            ZipInstall($_POST["zip-name"]);
            break;
    }
} else if($_POST["update"]) {
    $name = $_POST["update"];
} else if($_POST["setting"]) {
    $setting = $_POST["setting"];
} else if($_POST["uninstall"]) {
    $name = $_POST["uninstall"];
} else if($_POST["list"]) {
    // 
}

if(empty($return)) {
    $responce_code = 400;
    $return = "No message available.";
}
echo json_encode([
    "code" => $responce_code ?? 200,
    "message" => $return
],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);