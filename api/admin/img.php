<?php
/*
BlitZlog api[img] v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$api = true;
$admin = true;
$Folder = "../../data/img";
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

function fileUpload($filePost) {
    $fileAry = [];
    $fileCount = count($filePost["name"]);
    $fileKeys = array_keys($filePost);

    for($i = 0;$i < $fileCount;$i++) {
        foreach($fileKeys as $key) {
            $fileAry[$i][$key] = $filePost[$key][$i];
        }
    }

    return $fileAry;
}
if(isset($_FILES["file"])) {
    $uploadOK = $uploadNG = 0;
    $file = fileUpload($_FILES["file"]);
    foreach($file as $File) {
        $fileName = basename($File["name"]);
        if(is_uploaded_file($File["tmp_name"])) {
            $mimeType = mime_content_type($File["tmp_name"]);
            $isImage = str_contains($mimeType,"image/");
            $isValid = $File["error"] === 0 && $isImage;
            
            if($isValid) {
                ++$uploadOK;
                move_uploaded_file($File["tmp_name"],"$Folder/$fileName");
            } else {
                ++$uploadNG; 
            }
        }
    }
    if($uploadNG === 0 && $uploadOK > 0) {
        $return = "Upload success.";
    } elseif($uploadNG > 0 && $uploadOK > 0) {
        $return = "Some file uploads failed.";
    } elseif($uploadNG > 0 && $uploadOK === 0) {
        $return = "Upload failed.";
    } else {
        $return = "Upload file is not found.";
    }
} else if(isset($_POST["rmv"])) {
    $fileName = $_POST["rmv"];
    if(!preg_match("/^[a-zA-Z0-9_\-\.]+$/",$fileName) || strpos($fileName,"..") !== false || substr($fileName,0,1) === ".") {
        $responce_code = 400;
        $return = "Invalid file name provided for deletion.";
    } else {
        $file = basename($fileName);
        if(is_file("$Folder/$file")) {
            if(unlink("$Folder/$file")) {
                $return = "$file is deleted.";
            } else {
                $return = "$file is delete failed.";
            }
        } else {
            $return = "$file is not found.";
        }
    }
} else if(isset($_POST["read"])) {
    $fileName = $_POST["read"];
    if(!preg_match("/^[a-zA-Z0-9_\-\.]+$/",$fileName) || strpos($fileName,"..") !== false || substr($fileName,0,1) === ".") {
        $responce_code = 400;
        $return = "Invalid file name provided for read.";
    } else {
        $file = basename($fileName);
        if(is_file("$Folder/$file")) {
            $return = formatFile($file);
        } else {
            $return = "$file is not found.";
        }
    }
} else if(isset($_POST["list"])) {
    foreach(scandir("$Folder/") as $file) {
        if($file == "." || $file == ".." || $file == ".htaccess") continue;
        $return[] = formatFile($file);
    }
    if(empty($return)) {
        $return = "There are no files in the file folder.";
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

function formatFile($file) {
    global $Folder;
    return [
        "name" => $file,
        "created_at" => date("Y-m-d H:i:s",filectime("$Folder/$file")),
        "updated_at" => date("Y-m-d H:i:s",filemtime("$Folder/$file")),
        "size" => filesize("$Folder/$file"),
        "mime" => mime_content_type("$Folder/$file"),
    ];
}