<?php
/*
BlitZlog api[article] v0.4.0-beta
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
    sleep(1.5);
    $title = htmlspecialchars($_POST["title"]);
    $content = $_POST["content"];
    $category = (int)$_POST["category"];
    $img = basename($_POST["img"] ?? null);
    $status = $_POST["status"] ?? 0;
    $reserved = date("Y-m-d H:i:s",strtotime($_POST["reserved_at"]) ?? null);
    $customUrl = urlencode($_POST["custom_url"] ?? null);
    $customUrl = generateUniqueSlug($customUrl);
    $users = $login_user_id ?? 0;
    $now = date("Y-m-d H:i:s");

    $stmt = $db->prepare("INSERT INTO article (title, content, category, img, users, status, created_at, updated_at, reserved_at, custom_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1,$title);
    $stmt->bindValue(2,$content);
    $stmt->bindValue(3,$category);
    $stmt->bindValue(4,$img);
    $stmt->bindValue(5,$users);
    $stmt->bindValue(6,$status);
    $stmt->bindValue(7,$now);
    $stmt->bindValue(8,$now);
    $stmt->bindValue(9,$reserved);
    $stmt->bindValue(10,$customUrl);

    if($stmt->execute()) {
        $responce_code = 200;
        $return = "create success.";
    } else {
        $responce_code = 400;
        $return = "create failed.";
    }
} else if(isset($_POST["edit"])) {
    sleep(1.5);
    $id = (int)$_POST["id"];
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category = (int)$_POST["category"];
    $img = basename($_POST["img"] ?? null);
    $status = $_POST["status"] ?? 0;
    $reserved = date("Y-m-d H:i:s",strtotime($_POST["reserved_at"]) ?? null);
    $customUrl = urlencode($_POST["custom_url"] ?? null);
    $customUrl = generateUniqueSlug($customUrl);
    $now = date("Y-m-d H:i:s");

    $stmt = $db->prepare("UPDATE article SET title = ?, content = ?, category = ?, img = ?, status = ?, updated_at = ?, reserved_at = ?, custom_url = ? WHERE id = ?");
    $stmt->bindValue(1,$title);
    $stmt->bindValue(2,$content);
    $stmt->bindValue(3,$category);
    $stmt->bindValue(4,$img);
    $stmt->bindValue(5,$status);
    $stmt->bindValue(6,$now);
    $stmt->bindValue(7,$reserved);
    $stmt->bindValue(8,$customUrl);
    $stmt->bindValue(9,$id);

    if($stmt->execute()) {
        $responce_code = 200;
        $return = "update success.";
    } else {
        $responce_code = 400;
        $return = "update failed.";
    }
} else if(isset($_POST["remove"])) {
    sleep(1.5);
    $id = (int)$_POST["rmv"];

    $stmt = $db->prepare("DELETE FROM article WHERE id = ?");
    $stmt->bindValue(1,$id);
    
    if($stmt->execute()) {
        $responce_code = 200;
        $return = "delete success.";
    } else {
        $responce_code = 400;
        $return = "delete failed.";
    }
} else if(isset($_POST["read"])) {
    $id = (int)$_POST["read"];
    $stmt = $db->prepare("SELECT a.*, c.name AS category_name FROM article AS a LEFT JOIN category AS c ON a.category = c.id WHERE a.id = :id");
    $stmt->bindValue(":id",$id);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    
    if($row) {
        $responce_code = 200;
        $return = formatArticle($row);
    } else {
        $responce_code = 400;
        $return = "Article not found.";
    }
} else if(isset($_POST["list"])) {
    $res = $db->query("SELECT a.*, c.name AS category_name FROM article AS a LEFT JOIN category AS c ON a.category = c.id");
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $return[] = formatArticle($row);
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

function formatArticle($a) {
    return [
        "id" => $a["id"],
        "title" => $a["title"],
        "content" => $a["content"],
        "img" => $a["img"] ?? null,
        "status" => $a["status"] ?? null,
        "created_at" => date("Y-m-d H:i", strtotime($a["created_at"])),
        "updated_at" => date("Y-m-d H:i", strtotime($a["updated_at"])),
        "reserved_at" => $a["reserved_at"] ? date("Y-m-d H:i", strtotime($a["reserved_at"])) : null,
        "custom_url" => $a["custom_url"] ?? null,
        "category" => [
            "id" => $a["category"],
            "title" => $a["category_name"] ?? "---"
        ]
    ];
}
function generateUniqueSlug($baseSlug) {
    $slug = $baseSlug;
    $counter = 1;
    while(slugExists($slug)) {
        $slug = $baseSlug."-".$counter;
        $counter++;
    }
    return $slug;
}
function slugExists($slug) {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE custom_url = :slug");
    $stmt->execute(["slug" => $slug]);
    return $stmt->fetchColumn() > 0;
}