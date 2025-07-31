<?php
/*
BlitZlog Module[generate] v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
$options = getopt("",[
    "name:",
    "author:",
    "category::"
]);
$name = $options["name"] ?? "sample";
$slug = strtolower($name);
$dir = __DIR__."/$slug/";

if(!file_exists($dir)) {
    mkdir($dir,0777,true);

    $category = $options["category"] ?? "Utility";
    file_put_contents($dir."about.php",<<<eof
    <?php
    \$modules["$slug"]["about"] = [
        "name" => "$name",
        "category" => "{$category}",
        "author" => "{$options["author"]}",
        "version" => "0.0.0",
        "description" => "$name module",
        "API_LEVEL" => 1,
    ];
    eof);

    echo "✅ Module '$name' created at module/$slug/\n";
} else {
    echo "⚠️ Module directory already exists: module/$slug/\n";
}