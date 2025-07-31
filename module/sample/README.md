# Sample Module ガイダンス
BlitZlogのSample Moduleへようこそ。<br>
ここではモジュールの仕組みを詳しく説明します。

## 作る前に
まずBlitZlogのモジュールは**配列方式**となっており、WordPressやPluck CMSのような**定数、関数方式**は非対応です。<br>
また、`$moduleName`でモジュール名を指名しなければなりません。

## about (*)
これはモジュールの概要等を説明するものです。
```php
$modules[$moduleName]["about"] = [
    "name" => "Sample Module",
    "img" => null,
    "category" => "Utility",
    "author" => "BlitZlog",
    "version" => "0.0.0",
    "description" => "This is a sample module for BlitZlog,demonstrating how to create a module with basic functionality.",
    "website" => "https://example.com/sample-module",
    "API_LEVEL" => 1,
];
```
name [String]: モジュール名称<br>
img [String | Null]: モジュールのロゴや画像<br>
category [String]: モジュールの分類<br>
author [String]: 作成者など<br>
version [String]: バージョン（メジャー.マイナー.パッチといった書き方を推奨します）<br>
description [String]: モジュールの簡単な説明<br>
website [String | Null]: 作成者のウェブサイト<br>
API_LEVEL [Number]: モジュールAPIのレベル<br>

## code
これは閲覧部分のheadタグに追加するhtmlコードです。
```php
$modules[$moduleName]["code"] = <<<eof
    <script>
        console.log("Sample Module Loaded");
    </script>
eof;
```
この例ではEOFを使用していますが、"や'などでも可能です。

## admin_code
これは管理画面部分のheadタグに追加するhtmlコードです。
```php
$modules[$moduleName]["admin_code"] = <<<eof
    <script>
        console.log("Sample Module Admin Code Loaded");
    </script>
eof;
```
この例ではEOFを使用していますが、"や'などでも可能です。

## page
これは閲覧部分に新しくページを追加するものです。
```php
$modules[$moduleName]["page"] = function($id) {
    return [
        "title" => "Sample Page",
        "main" => "<h1>Sample Page</h1><p>This is a sample page for the Sample Module with ID: $id</p>",
        "pankuzu" => "Sample Module > Sample Page",
    ];
};
```
id: /$moduleName/〈引数〉からの〈引数〉の値<br>
return {<br>
    title: ページのタイトル<br>
    main: メイン部分に出るhtmlコード<br>
    pankuzu: 今いる場所<br>
}

## admin_page
これは管理画面部分に新しくページを追加するものです。
```php
$modules[$moduleName]["admin_page"] = function($id) {
    return [
        "title" => "Sample Admin Page",
        "main" => "<h1>Sample Admin Page</h1><p>This is a sample page for the Sample Admin Module with ID: $id</p>",
        "pankuzu" => "Sample Admin Module > Sample Admin Page",
    ];
};
```
id: /$moduleName/〈引数〉からの〈引数〉の値<br>
return {<br>
    title: ページのタイトル<br>
    main: メイン部分に出るhtmlコード<br>
    pankuzu: 今いる場所<br>
}

## settings
これはモジュールの設定項目です。
```php
$modules[$moduleName]["settings"] = [
    "sample_setting" => [
        "type" => "select",
        "label" => "Sample Setting",
        "option" => [
            "Select 1",
            "Select 2",
            "Select 3",
        ],
        "description" => "This is a sample setting for the Sample Module.",
        "default" => "Default Value",
    ],
];
```

〈設定名〉{<br>
    type: 設定のinputタグのタイプ<br>
    label: 設定項目の名称<br>
    option: 設定のオプション項目（selectやcheckbox、radio等で使用）<br>
    description: 設定項目の説明<br>
    default: デフォルト値<br>
}

----
# その他
## category
Utility、Editor、Design、SNS、Security、Other
のみ対応です。

## API_LEVEL
モジュールの互換性バージョンです。
BlitZlog内部にはAPI_LEVELが存在し、それが合えばモジュールが動作する仕組みです。
0.4.0は**1**ですが、大きなアップデートとかがあり、仕様変更されていくごとに数値が上がります。