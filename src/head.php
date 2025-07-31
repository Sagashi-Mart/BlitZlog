<?php
/*
BlitZlog head v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
session_start();
$i = 0;
$protocol = $_SERVER["SERVER_PROTOCOL"] ?? "HTTP/1.0";
$host = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"];
$scriptName = $_SERVER["SCRIPT_NAME"] ?? "";
$documentRoot = $_SERVER["DOCUMENT_ROOT"] ?? "";
$currentDir = str_replace(DIRECTORY_SEPARATOR,"/",realpath(__DIR__));
$baseDir = str_replace("/src","",$currentDir);
$basePath = str_replace(str_replace(DIRECTORY_SEPARATOR,"/",$documentRoot),"",$baseDir);
$ps = rtrim($basePath,"/")."/";

$random = date("YmdHis").rand(1000,9999);

if(!function_exists("str_contains")) {
	/**
	 * Determine if a string contains a given substring.
	 * PHP 8.0's str_contains() polyfill.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for.
	 * @return bool Returns true if the $needle is found in the $haystack, false otherwise.
	 */
	function str_contains(string $haystack,string $needle): bool
	{
		return $needle === "" || strpos($haystack, $needle) !== false;
	}
}