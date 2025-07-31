<?php
$modules["random"]["about"] = [
    "name" => "ランダムカラー",
    "category" => "Utility",
    "author" => "ゆー",
    "version" => "0.1",
    "description" => "ランダムカラーのやつ",
    "website" => null,
    "update_url" => null,
    "API_LEVEL" => 1,
];
$modules["random"]["page"] = function($id) {
    global $ps;
    if($id === "rgb") {  
        $func = randomColor();
        $color = "rgb($func)";
    } else if($id === "rgba") {
        $func = randomColor(1);
        $color = "rgba($func)";
    } else if($id === "hsl") {
        $func = randomColor(3);
        $color = "hsl($func)";
    } else if($id === "hexa") {
        $func = randomColor(4);
        $color = "#$func";
    } else {
        $func = randomColor(2);
        $color = "#$func";
    }
    return [
        "title" => "Random Color",
        "main" => <<<eof
        <a onclick="location.reload()" class="btn primary">Reload!</a>
        <a href="{$ps}random/rgb" class="btn secondary">RGB</a>
        <a href="{$ps}random/rgba" class="btn secondary">RGBA</a>
        <a href="{$ps}random/hsl" class="btn secondary">HSL</a>
        <a href="{$ps}random/" class="btn secondary">HEX</a>
        <a href="{$ps}random/hexa" class="btn secondary">HEXA</a>
        <p>$color</p>
        <div style="background-color: $color;width: 100%;height: 100px;"></div>
        eof,
        "pankuzu" => "ランダムカラーモジュール",
    ];
};
function randomColor($type = 0) {
    if($type === 1) {
        return random_int(0,255).",".random_int(0,255).",".random_int(0,255).",".(random_int(1,10) / 10);
    } else if($type === 2) {
        return substr(str_shuffle("0123456789ABCDEF"),0,6);
    } else if($type === 3) {
        return random_int(0,360)."deg ".random_int(0,100)."% ".random_int(0,100)."%";
    } else if($type === 4) {
        return substr(str_shuffle("0123456789ABCDEF"),0,6).substr(str_shuffle("0123456789"),0,2);
    }
    return random_int(0,255).",".random_int(0,255).",".random_int(0,255);
}