<?php
//error_reporting(0);
function blogHeader() {
    global $Blog;
    global $ps;
    global $db;
    $blogSearch = "";
    if($Blog["search"] === 1) {
        $blogSearch = <<<eof
        <form class="search-bar" action="{$ps}search/" method="GET">
            <input type="text" name="q" placeholder="探したいキーワードを入力...">
            <button type="submit">検索</button>
        </form>
        eof;
    }
    echo <<<eof
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="{$ps}">{$Blog["name"]}</a>
            </div>
            <!-- ハンバーガーメニュー（スマホ表示） -->
            <button class="menu-toggle d-md-none">
                <i class="bi bi-list"></i> <!-- ハンバーガーアイコン -->
            </button>
            <nav class="main-navigation">
                <ul class="menu-list">
                    <li><a href="{$ps}">ホーム</a></li>
                    <li class="has-submenu">
                        <a href="categories.html">カテゴリ</a>
                        <ul class="submenu" style="display:none;">
                            <li><a href="tech.html">テクノロジー</a></li>
                            <li><a href="lifestyle.html">ライフスタイル</a></li>
                            <li><a href="science.html">サイエンス</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">このブログについて</a></li>
                    <li><a href="contact.html">お問い合わせ</a></li>
                    $blogSearch
                </ul>
            </nav>
        </div>
        <div class="offcanvas-menu" id="offcanvasMenu">
            <div class="offcanvas-header">
            <h2>メニュー</h2>
                <button class="menu-close">
                    <i class="bi bi-x"></i> <!-- 閉じるアイコン -->
                </button>
            </div>
            <ul class="menu-list-sp">
                <li><a href="{$ps}">ホーム</a></li>
                <li class="has-submenu">
                    <a href="categories.html">カテゴリ</a>
                    <ul class="submenu">
                        <li><a href="tech.html">テクノロジー</a></li>
                        <li><a href="lifestyle.html">ライフスタイル</a></li>
                        <li><a href="science.html">サイエンス</a></li>
                    </ul>
                </li>
                <li><a href="about.html">このブログについて</a></li>
                <li><a href="contact.html">お問い合わせ</a></li>
                $blogSearch
            </ul>
        </div>
    </header>
    eof;
}
function blogFooter() {
    global $Blog;
    global $Link;
    global $Flw;
    global $DATA;

    $nowtime = date("Y年m月d日 H時i分s秒");
    echo <<<eof
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4>説明</h4>
                <p>{$Blog["about"]}</p>
            </div>
            <div class="footer-section">
                <h4>クイック記事</h4>
                <ul>
                    $Link
                </ul>
            </div>
            <div class="footer-section">
                <h4>フォロー</h4>
                <ul class="social-links">
                    $Flw
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>{$DATA["copyright"]}　Themes: <a href="//sagashi0120.cloudfree.jp/blitzlog/?themes={$Blog["theme"]}">{$Blog["theme"]}</a>　VERSION: {$DATA["version"]}　ACCESS_DATE: {$nowtime}</p>
        </div>
    </footer>
    eof;
}
function article($mode,$id) {
    global $db;
    global $Blog;
    global $DATA;
    global $ps;
    $limit = 25;
    if($mode === "top") {
        echo "<div class='content'>
        <h1>ホーム</h1>";

        $perPage = $Blog["perPage"];
        $page = isset($id) ? $id : 1;
        $offset = ($page - 1) * $perPage;

        $query = $db->query("SELECT * FROM article ORDER BY id DESC LIMIT $perPage OFFSET $offset");
        $totalArticles = $db->querySingle("SELECT COUNT(*) FROM article");
        $totalPages = ceil($totalArticles / $perPage);

        while($article = $query->fetchArray()) {
            $title = htmlspecialchars($article["title"]);
            $content = strip_tags($article["content"]);
            if(mb_strlen($content) > $limit) {
                $content = mb_substr($content,0,$limit)."…";
            }

            echo <<<eof
            <div class="article-card">
                <div class="card-image">
                    <img src="data/upload/{$article["img"]}" alt="記事のタイトル">
                </div>
                <div class="card-content">
                    <h3 class="card-title">$title</h3>
                    <p class="card-excerpt">$content</p>
                    <a href="article/{$article["id"]}" class="read-more">続きを読む</a>
                </div>
            </div>
            eof;
        }

        $back = $page - 1;
        $next = $page + 1;

        echo <<<eof
        <div class="pagination">
        eof;
        if($page > 1) {
            echo <<<eof
            <a href="?page=$back">≪</a>
            eof;
        }
        for($i = 1;$i <= $totalPages;$i++) {
            if($i === $page) {
                echo <<<eof
                <a href="?page=$i" class="active">$i</a>
                eof;
            } else {
                echo <<<eof
                <a href="?page=$i">$i</a>
                eof;
            }
        }
        if($page < $totalPages) {
            echo <<<eof
            <a href="?page=$next">≫</a>
            eof;
        }
        echo "</div>
        </div>";
    } elseif($mode === "rss") {
        $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        header("Content-Type: application/xml");
        echo <<<eof
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <title>{$Blog["name"]}</title>
                <link>$url</link>
                <description>{$Blog["about"]}</description>
                <language>ja</language>
                <copyright>{$DATA["copyright"]}</copyright>
        eof;

        $query = $db->query("SELECT * FROM article");
        while($article = $query->fetchArray()) {
            $pubDate = date(DATE_RSS,strtotime($article["created_at"]));
            $Link = str_replace("?rss","",(empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
            
            $content = strip_tags($article["content"]);
            if(mb_strlen($content) > $limit) {
                $content = mb_substr($content,0,$limit)."…";
            }
            echo <<<eof
            <item>
                <title>{$article["title"]}</title>
                <link>{$Link}article/{$article["id"]}</link>
                <description>{$content}</description>
                <category>{$article["category"]}</category>
                <pubDate>{$pubDate}</pubDate>
            </item>
            eof;
        }

        echo <<<eof
            </channel>
        </rss>
        eof;
    } elseif($mode === "search") {
        $keyword = htmlspecialchars($id);
        echo <<<eof
        <div class="content">
        <h1>「{$keyword}」の検索結果</h1>
        <form class="search-form" action="{$ps}search/" method="GET">
            <input type="text" name="q" class="search-input" value="{$keyword}" placeholder="探したいキーワードを入力..." />
            <button type="submit" class="search-button">検索</button>
        </form>
        eof;

        if(!($keyword == "")) {
            $sql = "SELECT * FROM article WHERE title LIKE :keyword OR content LIKE :keyword";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(":keyword","%".$keyword."%");
            $result = $stmt->execute();
            $count = 0;
            $articles = [];
            while($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $articles[] = $row; // 結果を配列に格納
                $count++;
            }
    
            if($count > 0) {
                echo "<p>{$count}件、記事が見つかりました。</p>";
            } else {
                echo "<p>記事が見つかりませんでした。</p>";
            }
    
            foreach($articles as $article) {
                $title = htmlspecialchars($article["title"]);
                $content = strip_tags($article["content"]);
                if(mb_strlen($content) > $limit) {
                    $content = mb_substr($content,0,$limit)."…";
                }
    
                echo <<<eof
                <div class="article-card">
                    <div class="card-image">
                        <img src="data/upload/{$article["img"]}" alt="記事のタイトル">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">$title</h3>
                        <p class="card-excerpt">$content</p>
                        <a href="article/{$article["id"]}" class="read-more">続きを読む</a>
                    </div>
                </div>
                eof;
            }
        } else {
            echo <<<eof
            <p>検索キーワードが入力されていません。</p>
            eof;
        }
        echo "</div>";
    } elseif($mode === "article") {
        //
    }
}
function exportDatabaseToJson($dbFile,$outputFile) {
    try {
        // SQLiteデータベースに接続
        $db = new PDO("sqlite:" . $dbFile);

        // すべてのテーブル名を取得
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

        $backupData = [];

        // 各テーブルのデータを取得
        foreach($tables as $table) {
            $data = $db->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            $backupData[$table] = $data;
        }

        // JSON形式にエクスポート
        $json = json_encode($backupData,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // ファイルに保存
        file_put_contents($outputFile, $json);

        echo "バックアップが成功しました！ファイル: $outputFile\n";
    } catch(Exception $e) {
        echo "バックアップ中にエラーが発生しました: ".$e->getMessage();
    }
}
function importJsonToDatabase($dbFile,$jsonFile) {
    try {
        // SQLiteデータベースに接続
        $db = new PDO("sqlite:" . $dbFile);

        // JSONファイルを読み込み
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData,true); // 配列形式にデコード

        // トランザクション開始
        $db->beginTransaction();

        // 各テーブルのデータを挿入
        foreach($data as $table => $rows) {
            foreach($rows as $row) {
                // カラム名と値を分ける
                $columns = implode(", ",array_keys($row));
                $placeholders = ":".implode(", :",array_keys($row));

                // 挿入クエリを準備
                $stmt = $db->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

                // 値をバインドして実行
                foreach($row as $key => $value) {
                    $stmt->bindValue(":".$key,$value);
                }

                $stmt->execute();
            }
        }

        // トランザクション終了
        $db->commit();

        echo "データのインポートが成功しました！\n";
    } catch(Exception $e) {
        // エラー発生時はロールバック
        $db->rollBack();
        echo "インポート中にエラーが発生しました: ".$e->getMessage();
    }
}