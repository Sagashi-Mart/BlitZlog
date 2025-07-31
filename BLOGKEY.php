<?php
error_reporting(0);
require_once("src/sqlite.php");
function generateKey($length = 1000) {
    global $Blog,$DATA;
    $bin2 = bin2hex(random_bytes($length / 2));
    $time = microtime(true);
    $Key = "{$Blog['name']},{$time},{$length},{$DATA['version']},{$bin2}";

    $Hash = password_hash($Key,PASSWORD_BCRYPT);
    return [
        "key" => $Key,
        "hash" => $Hash
    ];
}
$lg = random_int(500,1500);
$key = generateKey($lg);
$id = 0;
$stmt = $db->prepare("UPDATE DATA SET BLOG_KEY = :key WHERE id = :id");
$stmt->bindValue(":key",$key["hash"]);
$stmt->bindValue(":id",$id);
if($stmt->execute() && password_verify($key["key"],$key["hash"]) && file_put_contents("data/blogKey.env",$key["key"])) {
    echo "blogKEY create success!";
} else {
    echo "blogKEY create failed...";
}