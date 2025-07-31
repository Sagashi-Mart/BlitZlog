<?php
/*
BlitZlog api[category] v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$api = true;
$admin = true;
$returnText = [];
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
if(isset($_POST["create"])) {
    $name = htmlspecialchars($_POST["name"]) ?? "Untitled";
    $status = (int)$_POST["status"] ?? 0;
    $now = date("Y-m-d H:i:s");

    $stmt = $db->prepare("INSERT INTO category (name, status, created_at, updated_at) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1,$name);
    $stmt->bindValue(2,$status);
    $stmt->bindValue(3,$now);
    $stmt->bindValue(4,$now);

    if($stmt->execute()) {
        $responce_code = 200;
        $return = "Category create.";
    } else {
        $responce_code = 400;
        $return = "Category create failed.";
    }
} else if(isset($_POST["edit"])) {
    $id = (int)$_POST["id"];
    $name = htmlspecialchars($_POST["name"]) ?? "Untitled";
    $status = (int)$_POST["status"] ?? 0;
    $now = date("Y-m-d H:i:s");

    $stmt = $db->prepare("UPDATE category SET name = ?, status = ?, updated_at = ? WHERE id = ?");
    $stmt->bindValue(1,$name);
    $stmt->bindValue(2,$status);
    $stmt->bindValue(3,$now);
    $stmt->bindValue(4,$id);

    if($stmt->execute()) {
        $responce_code = 200;
        $return = "Category update.";
    } else {
        $responce_code = 400;
        $return = "Category update failed.";
    }
} else if(isset($_POST["remove"])) {
    $id = (int)$_POST["rmv"];

    $stmt = $db->prepare("DELETE FROM category WHERE id = ?");
    $stmt->bindValue(1, $id);
    
    if($stmt->execute()) {
        $responce_code = 200;
        $return = "Category delete.";
    } else {
        $responce_code = 400;
        $return = "Category delete failed.";
    }
} else if(isset($_POST["read"])) {
    $id = (int)$_POST["read"];
    $stmt = $db->prepare("SELECT * FROM category WHERE id = :id");
    $stmt->bindValue(":id",$id);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    
    if($row) {
        $responce_code = 200;
        $return = formatCategory($row);
    } else {
        $responce_code = 400;
        $return = "Category not found.";
    }
} else if(isset($_POST["list"])) {
    $res = $db->query("SELECT * FROM category ORDER BY id DESC");
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $return[] = formatCategory($row);
    }
}

if(empty($return)) {
    $responce_code = 400;
    $return = "No message available.";
}
echo json_encode([
    "code" => $responce_code ?? 200,
    "message" => $return
],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

function formatCategory($a) {
    return [
        "id" => $a["id"],
        "name" => $a["name"],
        "status" => $a["status"] ?? null,
        "created_at" => date("Y-m-d H:i",strtotime($a["created_at"])),
        "updated_at" => date("Y-m-d H:i",strtotime($a["updated_at"])),
    ];
}