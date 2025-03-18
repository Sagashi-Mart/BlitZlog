<?php
require("php/sqlite.php");
function generateBlogKey($length = 1000) {
    return bin2hex(random_bytes($length / 2));
}
$blogKey = generateBlogKey();
$hashKey = password_hash($blogKey,PASSWORD_BCRYPT);
$id = 0;
$stmt = $db->prepare("UPDATE DATA SET BLOG_KEY = :key WHERE id = :id");
$stmt->bindValue(":key",$blogKey);
$stmt->bindValue(":id",$id);
if($stmt->execute() && password_verify($blogKey,$hashKey) && file_put_contents("data/blogKey.env",$hashKey)) {
    echo "success";
} else {
    echo "failed";
}
//unlink("BLOGKEY.php");