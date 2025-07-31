<?php
/*
BlitZlog v0.3.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
if(isset($_SESSION["blog_manager_login"])) {
	$adminLogin = true;
} else {
	$adminLogin = false;
}