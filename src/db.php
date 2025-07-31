<?php
/*
BlitZlog database v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
require(__DIR__."/db/adapter.php");

function parseEnvFile($filePath) {
    $envVars = [];
    if(!file_exists($filePath)) {
        throw new Exception(".env file not found at: ".$filePath);
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if(empty($line) || str_starts_with($line,"#")) {
            continue;
        }
        $parts = explode("=",$line,2);
        if(count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if(str_starts_with($value,'"') && str_ends_with($value,'"')) {
                $value = substr($value,1,-1);
            }
            if(str_starts_with($value,"'") && str_ends_with($value,"'")) {
                $value = substr($value,1,-1);
            }
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            $envVars[$key] = $value;
        }
    }
    return $envVars;
}
try {
    $envFilePath = $documentRoot.$ps."data/database.env";
    $envVariables = parseEnvFile($envFilePath);
} catch(Exception $e) {
    error_log("Error loading .env file: ".$e->getMessage());
    die("Application configuration error.");
}

$DB = [];
$DB_TYPE = $_ENV["DB_TYPE"] ?? null;
if($DB_TYPE === "mysql") {
    $DB = [
        "type" => "mysql",
        "host" => $_ENV["MYSQL_HOST"] ?? "localhost",
        "name" => $_ENV["MYSQL_NAME"] ?? "",
        "user" => $_ENV["MYSQL_USER"] ?? "",
        "pass" => $_ENV["MYSQL_PASS"] ?? "",
    ];
} elseif($DB_TYPE === "sqlite") {
    $sqliteRelativePath = $_ENV["SQLITE_PATH"] ?? "";
    $DB["path"] = dirname(__DIR__)."/".$sqliteRelativePath; 
    $DB["type"] = "sqlite";
} else {
    error_log("Database type not defined or unsupported in .env: ".$DB_TYPE);
    die("Database type configuration error.");
}

global $db;
try {
    $db = DbFactory::createAdapter($DB);
} catch(Exception $e) {
    error_log("Database connection failed: ".$e->getMessage());
    die("Database connection error: ".$e->getMessage());
}

$query01 = $db->query("SELECT * FROM setting");
$query02 = $db->query("SELECT * FROM data");
$query03 = $db->query("SELECT * FROM quicklink WHERE visibility = 1 ORDER BY position ASC");
$query04 = $db->query("SELECT * FROM follow WHERE visibility = 1 ORDER BY position ASC");
foreach($query01->fetch(PDO::FETCH_ASSOC) as $key => $value) {
    $Blog[$key] = isset($value) ? htmlspecialchars($value,ENT_QUOTES,"UTF-8") : null;
}
foreach($query02->fetch(PDO::FETCH_ASSOC) as $key => $value) {
    $DATA[$key] = isset($value) ? htmlspecialchars($value,ENT_QUOTES,"UTF-8") : null;
}
while($result03 = $query03->fetch(PDO::FETCH_ASSOC)) {
    $linkName = htmlspecialchars($result03["name"]);
    $Link .= <<<eof
    <li>
        <a href="article?id={$result03["article"]}">$linkName</a>
    </li>
    eof;
}
while($result04 = $query04->fetch(PDO::FETCH_ASSOC)) {
    $FlwName = htmlspecialchars($result04["platform"]);
    $Flw .= <<<eof
    <li>
        <a href="//{$result04["link"]}" target="_blank"><i class="bi bi-{$result04["icon"]}"></i> $FlwName</a>
    </li>
    eof;
}
date_default_timezone_set($Blog["timezone"]);
$Head = <<<eof
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=IBM Plex Sans JP&display=swap" />
    <link rel="stylesheet" href="{$ps}css/style.css?$random" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{$ps}theme/{$Blog["theme"]}/style.css?$random" />
    <script src="{$ps}js/jquery-3.7.1.min.js"></script>
    <script src="{$ps}js/main.blitzlog.js?$random"></script>
    
    <meta name="description" content="{$Blog["about"]}">\n
eof;