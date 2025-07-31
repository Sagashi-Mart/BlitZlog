<?php
/*
BlitZlog block[content] v0.4.0-beta
(c) 2022-2025 Sagashi Mart.
*/

$blockPlugin["content"]["about"] = [
    "name" => "目次生成",
    "author" => "BlitZlog",
    "version" => "1.0.0",
    "description" => "このブロックは、記事の目次を自動的に生成します。見出しタグ（h1, h2, h3など）を解析し、目次を作成します。",
    "website" => "https://example.com/content-block",
    "update_url" => null,
    "API_LEVEL" => 1,
];

$result = headingHTML($content); 
$headingProcess = $result["html_with_ids"];
if(str_contains($headingProcess,"#content")) {
    $toc = generateTOC($result["headings"]);
    $headingProcess = str_replace("#content",$toc,$headingProcess);
}
$content = $headingProcess;

function headingHTML($html) {
    if(empty($html)) {
        return [
            "headings" => [],
            "html_with_ids" => ""
        ];
    }
    $dom = new DOMDocument("1.0","UTF-8");
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_encode_numericentity($html,[0x80,0x10FFFF,0,0xFFFF],"UTF-8"));
    libxml_clear_errors();
    $headings = [];
    $outputHTML = "";
    $body = $dom->getElementsByTagName("body")->item(0);
    if (!$body) {
        error_log("headingHTML: 'body' element not found in the provided HTML.");
        return [
            "headings" => [],
            "html_with_ids" => $html
        ];
    }
    foreach($body->childNodes as $node) {
        if(in_array($node->nodeName,["h1","h2","h3","h4","h5","h6"])) {
            $id = "heading-".$node->nodeName."-".count($headings);
            $node->setAttribute("id", $id);
            $headings[] = [
                "level" => (int)substr($node->nodeName,1),
                "content" => $node->textContent,
                "id" => $id
            ];
        }
        if($node->nodeName === "p" && $node->textContent === "#content") {
            $outputHTML .= "#content ";
            continue;
        }
        $outputHTML .= $dom->saveHTML($node);
    }
    return [
        "headings" => $headings,
        "html_with_ids" => $outputHTML
    ];
}
function generateTOC($headings) {
    $toc = "<div id='toc'>\n<b>目次</b>\n<ul>\n";
    $currentLevel = 1;

    foreach($headings as $heading) {
        while($heading["level"] > $currentLevel) {
            $toc .= "<ul>\n";
            $currentLevel++;
        }
        while($heading["level"] < $currentLevel) {
            $toc .= "</ul>\n";
            $currentLevel--;
        }
        $escaped_heading_content = htmlspecialchars($heading["content"],ENT_QUOTES,"UTF-8");
        $escaped_heading_id = urlencode($heading["id"]); 
        $toc .= "<li><a href='#{$escaped_heading_id}'>{$escaped_heading_content}</a></li>\n";
    }
    while($currentLevel > 1) {
        $toc .= "</ul>\n";
        $currentLevel--;
    }

    $toc .= "</ul></div>";
    return $toc;
}