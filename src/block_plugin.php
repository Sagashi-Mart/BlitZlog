<?php
/*
BlitZlog block_plugin v0.4.0
(c) 2025 yu-., Sagashi Mart.
*/

foreach(glob("module/__block/*.inc.php") as $file) {
	include_once($file);
}