<?php
$admin = true;
include_once("sqlite.php");
if(isset($_POST["save"])) {
    $id = 0;
    $name = $_POST["name"];
    $theme = $_POST["theme"];
    $perPage = $_POST["perPage"];
    $about = $_POST["about"];
    $timezone = $_POST["timezone"];
    $search = $_POST["search"];

    $stmt = $db->prepare("UPDATE setting SET blog_name = :name,theme = :theme,about = :about,timezone = :timezone,post_per_page = :perPage,search = :search WHERE id = :id");

    $stmt->bindValue(":id",$id,SQLITE3_INTEGER);
    $stmt->bindValue(":perPage",$perPage,SQLITE3_INTEGER);
    $stmt->bindValue(":search",$search,SQLITE3_INTEGER);

    $stmt->bindValue(":name",$name,SQLITE3_TEXT);
    $stmt->bindValue(":theme",$theme,SQLITE3_TEXT);
    $stmt->bindValue(":about",$about,SQLITE3_TEXT);
    $stmt->bindValue(":timezone",$timezone,SQLITE3_TEXT);
    if($stmt->execute()) {
        header("HTTP/1.1 200 OK");
    } else {
        header("HTTP/1.1 400 Bad Request");
    }
} else {
    header("HTTP/1.1 400 Bad Request");
}