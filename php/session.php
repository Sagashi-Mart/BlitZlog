<?php
session_start();
error_reporting(0);
$_SESSION["blog-manager-login"] = true;
if(!isset($_SESSION["file-manager-login"])) {
    header("Location: ./login.php");
    exit();
}
?>