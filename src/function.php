<?php
/*
BlitZlog function v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
const BLITZLOG_API_LEVEL = 1;
require(__DIR__."/head.php");
require(__DIR__."/db.php");

function blogHeader() {
	global $Blog,$ps,$db,$admin;
	$blogSearch = "";
	if($Blog["search"] == 1) {
		$blogSearch = <<<eof
		<search>
			<form class="search-bar" action="{$ps}search/" method="GET">
				<input type="search" name="q" placeholder="探したいキーワードを入力...">
				<button type="submit">検索</button>
			</form>
		</search>
		eof;
	}
	$query = $db->query("SELECT * FROM menu ORDER BY position ASC");
	$menu = "";
	$menus = [];
	while($row = $query->fetch(PDO::FETCH_ASSOC)) {
		$menus[] = $row;
	}
	foreach($menus as $menuDB) {
		if($menuDB["visibility"] == 0) {
			//
		} elseif($menuDB["title"] == "_CATEGORY_SETTINGS_") {
			$category = "";
			$query = $db->query("SELECT * FROM category ORDER BY id ASC");
			while($categoryDB = $query->fetch(PDO::FETCH_ASSOC)) {
				$categoryName = htmlspecialchars($categoryDB["name"],ENT_QUOTES,"UTF-8");
				$category .= <<<eof
				<li><a href="{$ps}category/{$categoryDB["id"]}">$categoryName</a></li>
				eof;
			}

			$menu .= <<<eof
			<li class="has-submenu">
				<a>カテゴリ</a>
				<ul class="submenu" style="display:none;">
					$category
				</ul>
			</li>
			eof;
		} else {
			$menuName = htmlspecialchars($menuDB["title"],ENT_QUOTES,"UTF-8");
			$menu .= <<<eof
			<li><a href="{$ps}article/{$menuDB["article"]}">$menuName</a></li>
			eof;
		}
	}
	if($admin === true) {
		echo <<<eof
		<header>
			<div class="header-container">
				<div class="logo">
					<a href="{$ps}admin/">{$Blog["cms_name"]} 管理画面</a>
				</div>
				<button class="menu-toggle d-md-none">
					<i class="bi bi-list"></i>
				</button>
				<nav class="main-navigation">
					<ul class="menu-list">
						<li><a href="{$ps}">{$Blog["cms_name"]}へ戻る</a></li>
						<li><a href="{$ps}admin/">Top</a></li>
					</ul>
				</nav>
			</div>
			<div class="offcanvas-menu" id="offcanvasMenu">
				<div class="offcanvas-header">
				<h2>メニュー</h2>
					<button class="menu-close">
						<i class="bi bi-x"></i>
					</button>
				</div>
				<ul class="menu-list-sp">
					<li><a href="{$ps}">{$Blog["cms_name"]}へ戻る</a></li>
					<li><a href="{$ps}admin/">Top</a></li>
					<li><a onclick="logout()">ログアウト</a></li>
				</ul>
			</div>
		</header>
		eof;
	} else {
		echo <<<eof
		<header>
			<div class="header-container">
				<div class="logo">
					<a href="{$ps}">{$Blog["cms_name"]}</a>
				</div>
				<button class="menu-toggle d-md-none">
					<i class="bi bi-list"></i>
				</button>
				<nav class="main-navigation">
					<ul class="menu-list">
						<li><a href="{$ps}">ホーム</a></li>
						$menu
						$blogSearch
					</ul>
				</nav>
			</div>
			<div class="offcanvas-menu" id="offcanvasMenu">
				<div class="offcanvas-header">
				<h2>メニュー</h2>
					<button class="menu-close">
						<i class="bi bi-x"></i>
					</button>
				</div>
				<ul class="menu-list-sp">
					<li><a href="{$ps}">ホーム</a></li>
					$menu
					$blogSearch
				</ul>
			</div>
		</header>
		eof;
	}
}
function blogFooter() {
	global $Blog,$Link,$Flw,$DATA,$ps,$admin;

	if($admin === true) {
		echo <<<eof
		<footer class="site-footer">
			<div class="footer-bottom">
				<p>{$DATA["copyright"]}<br>Themes: <a href="//sagashi0120.cloudfree.jp/blitzlog/?themes={$Blog["theme"]}">{$Blog["theme"]}</a></p>
			</div>
		</footer>
		eof;
	} else {
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
				<p>
					{$DATA["COPYRIGHT"]}<br>
					Themes: <a href="//sagashi0120.cloudfree.jp/blitzlog/?themes={$Blog["theme"]}">{$Blog["theme"]}</a>　
					<label><a href="{$ps}rss" class="rss-feed"><img src="{$ps}data/system/rss.png"> <span>RSS</span></a></label>
				</p>
			</div>
		</footer>
		eof;
	}
}
function main($mode,$id = null) {
	global $db,$Blog,$DATA,$ps,$protocol;

	$returnHTML = "";
	$returnTITLE = "";
	$limit = 25;
	switch($mode) {
		case "top":
			$returnTITLE = "ホーム";
			$returnHTML .= <<<eof
				<nav aria-label="breadcrumb">
					<ul class="breadcrumb">
						<li><i class="bi bi-house"></i> ホーム</li>
					</ul>
				</nav>
				<h1>ホーム</h1>
			eof;

			$perPage = (int)$Blog["post_per_page"];
			$page = isset($id) ? (int)$id : 1;
			$offset = ($page - 1) * $perPage;

			$stmt = $db->prepare("SELECT * FROM article ORDER BY id DESC LIMIT :limit OFFSET :offset");
			$stmt->bindValue(":limit",$perPage,PDO::PARAM_INT);
			$stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
			$stmt->execute();

			$stmt3 = $db->query("SELECT COUNT(*) FROM article");
			$query = $stmt3->fetch(PDO::FETCH_NUM);
			$totalArticles = (int)$query[0];
			$totalPages = ceil($totalArticles / $perPage);

			if(!($totalArticles > 0)) {
				$returnHTML .= "<p>まだ記事が投稿されていません。投稿されるまでしばらくお待ちください！</p>";
			}
			while($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$url = htmlspecialchars($article["custom_url"],ENT_QUOTES,"UTF-8");
				$title = htmlspecialchars($article["title"],ENT_QUOTES,"UTF-8");
				$content = preg_replace("/<[^>]*>/"," ",$article["content"]);
				$content = preg_replace("/\s+/"," ",$content);
				$content = trim($content);
				if(mb_strlen($content) > $limit) {
					$content = mb_substr($content,0,$limit)."…";
				}

				isset($article["img"]) ? $imgSrc = "data/img/{$article["img"]}" : $imgSrc = "data/system/no-image.png";

				$returnHTML .= <<<eof
				<div class="article-card">
					<div class="card-image">
						<img src="$imgSrc" alt="記事のタイトル">
					</div>
					<div class="card-content">
						<h3 class="card-title">$title</h3>
						<p class="card-excerpt">$content</p>
						<a href="article/$url" class="read-more">続きを読む</a>
					</div>
				</div>
				eof;
			}

			$back = $page - 1;
			$next = $page + 1;
			$returnHTML .= <<<eof
			<div class="pagination">
			eof;
			if($page > 1) {
				$returnHTML .= <<<eof
				<a href="?page=$back">≪</a>
				eof;
			}
			for($i = 1;$i <= $totalPages;$i++) {
				if($i === $page) {
					$returnHTML .= <<<eof
					<a href="?page=$i" class="active">$i</a>
					eof;
				} else {
					$returnHTML .= <<<eof
					<a href="?page=$i">$i</a>
					eof;
				}
			}
			if($page < $totalPages) {
				$returnHTML .= <<<eof
				<a href="?page=$next">≫</a>
				eof;
			}
			$returnHTML .= "</div>";
			break;
		case "rss":
			$url = htmlspecialchars($host.$_SERVER["REQUEST_URI"],ENT_QUOTES | ENT_XML1,"UTF-8");
			header("Content-Type: application/xml");
			$returnHTML .= <<<eof
			<?xml version="1.0" encoding="UTF-8"?>
			<rss version="2.0">
				<channel>
					<title>{$Blog["cms_name"]}</title>
					<link>$site</link>
					<docs>$url</docs>
					<description>{$Blog["about"]}</description>
					<language>ja</language>
					<copyright>{$DATA["COPYRIGHT"]}</copyright>
					<generator>BlitZlog</generator>
					<image>
						<url>{$site}{$ps}data/img/{$Blog["icon_url"]}</url>
						<title>{$Blog["cms_name"]}</title>
						<link>$site</link>
						<width>32</width>
						<height>32</height>
					</image>
			eof;

			$query = $db->query("SELECT * FROM article ORDER BY id DESC LIMIT 15");
			while($article = $query->fetch(PDO::FETCH_ASSOC)) {
				$categoryID = (int)$article["category"];
				$stmtCategory = $db->prepare("SELECT * FROM category WHERE id = :categoryId");
				$stmtCategory->bindValue(":categoryId", (int)$article["category"], SQLITE3_INTEGER);
				$stmtCategory->execute();
				$category = $stmtCategory->fetch(PDO::FETCH_ASSOC);

				$pubDate = date(DATE_RSS,strtotime($article["created_at"]));
				$Link = str_replace("rss","",$url);
				$custom_url = htmlspecialchars($article["custom_url"],ENT_QUOTES,"UTF-8");
				$content = purify_html($article["content"]);
				isset($article["img"]) ? $imgSrc = "{$site}{$ps}data/img/{$article["img"]}" : $imgSrc = "{$site}{$ps}data/system/no-image.png";
				$linkSrc = $Link."article/".$custom_url;

				$returnHTML .= <<<eof
				<item>
					<title>{$article["title"]}</title>
					<link>$linkSrc</link>
					<guid isPermaLink="true">$linkSrc</guid>
					<description><![CDATA[<p><img src="$imgSrc" class="thumbnail"></p>{$content}]]></description>
					<category id="$categoryID">{$category["name"]}</category>
					<pubDate>{$pubDate}</pubDate>
				</item>
				eof;
			}

			$returnHTML .= <<<eof
				</channel>
			</rss>
			eof;
			$returnTITLE = "RSS 2.0 Page";
			break;
		case "search":
			$keyword = isset($id) ? $id : null;
			$keywordDis = htmlspecialchars($keyword,ENT_QUOTES,"UTF-8");
			$returnTITLE = "検索「{$keywordDis}」";
			$returnHTML .= <<<eof
			<nav aria-label="breadcrumb">
				<ul class="breadcrumb">
					<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
					<li>検索 <i class="bi bi-caret-right-fill"></i></li>
					<li>{$keywordDis}</li>
				</ul>
			</nav>
			<h1>「{$keywordDis}」の検索結果</h1>
			<search>
				<form class="search-form" action="{$ps}search/" method="GET">
					<input type="search" name="q" class="search-input" value="{$keywordDis}" placeholder="探したいキーワードを入力...">
					<button type="submit" class="search-button">検索</button>
				</form>
			</search>
			eof;
			if(!empty($keyword)) {
				$perPage = $Blog["post_per_page"];
				$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
				$offset = ($page - 1) * $perPage;

				$keywordAs = trim(mb_convert_kana($keyword,"s"));
				$words = preg_split("/\s+/u",$keywordAs);
				$conditions = [];
				$params = [];
				foreach($words as $i => $word) {
					$conditions[] = "(title LIKE :kw_title_$i OR content LIKE :kw_content_$i)";
					$params[":kw_title_$i"] = "%{$word}%";
					$params[":kw_content_$i"] = "%{$word}%";
				}
				$countConditions = $conditions;
				$countStmt = $db->prepare("SELECT COUNT(*) FROM article WHERE ".implode(" AND ",$conditions));
				$countStmt->execute($params);
				$row = $countStmt->fetch(PDO::FETCH_NUM);
				$totalArticles = (int)$row[0];

				$stmt = $db->prepare("SELECT * FROM article WHERE ".implode(" AND ",$conditions)." ORDER BY id DESC LIMIT :limit OFFSET :offset");
				foreach($params as $key => $val) {
					$stmt->bindValue($key, $val);
				}
				$stmt->bindValue(":limit",$perPage,PDO::PARAM_INT);
				$stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
				$stmt->execute();

				$totalPages = ceil($totalArticles / $perPage);
				$articles = [];
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$articles[] = $row;
				}
				if($totalArticles > 0) {
					$returnHTML .= "<p>{$totalArticles}件の記事が見つかりました。</p>";
				} else {
					$returnHTML .= "<p>記事が見つかりませんでした。</p>";
				}
				foreach($articles as $article) {
					$url = htmlspecialchars($article["custom_url"],ENT_QUOTES,"UTF-8");
					$title = htmlspecialchars($article["title"],ENT_QUOTES,"UTF-8");
					$content = preg_replace("/<[^>]*>/"," ",$article["content"]);
					$content = preg_replace("/\s+/"," ",$content);
					$content = trim($content);
					if(mb_strlen($content) > $limit) {
						$content = mb_substr($content,0,$limit)."…";
					}
					isset($article["img"]) ? $imgSrc = "{$ps}data/img/{$article["img"]}" : $imgSrc = "{$ps}data/system/no-image.png";
					$returnHTML .= <<<eof
					<div class="article-card">
						<div class="card-image">
							<img src="$imgSrc" alt="記事のタイトル">
						</div>
						<div class="card-content">
							<h3 class="card-title">$title</h3>
							<p class="card-excerpt">$content</p>
							<a href="{$ps}article/$url" class="read-more">続きを読む</a>
						</div>
					</div>
					eof;
				}
				$back = $page - 1;
				$next = $page + 1;
				$returnHTML .= '<div class="pagination">';
				if($page > 1) {
					$returnHTML .= "<a href=\"{$ps}search/?q={$keywordDis}&page={$back}\">≪</a>";
				}
				for($i = 1; $i <= $totalPages; $i++) {
					if($i === $page) {
						$returnHTML .= "<a href=\"{$ps}search/?q={$keywordDis}&page={$i}\" class=\"active\">{$i}</a>";
					} else {
						$returnHTML .= "<a href=\"{$ps}search/?q={$keywordDis}&page={$i}\">{$i}</a>";
					}
				}
				if($page < $totalPages) {
					$returnHTML .= "<a href=\"{$ps}search/?q={$keywordDis}&page={$next}\">≫</a>";
				}
				$returnHTML .= "</div>";
			} else {
				$returnHTML .= <<<eof
				<p>検索キーワードが入力されていません。</p>
				eof;
			}
			break;
		case "category":
			$id = (int)$id;
			if(!empty($id)) {
				$stmt = $db->prepare("SELECT name FROM category WHERE id = :id");
				$stmt->bindValue(":id",$id);
				$stmt->execute();
				$category = $stmt->fetch(PDO::FETCH_ASSOC);
				if(empty($category)) {
					header("$protocol 404 Not Found");
					$returnTITLE = "カテゴリ不明";
					$returnHTML .= <<<eof
					<nav aria-label="breadcrumb">
						<ul class="breadcrumb">
							<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
							<li>カテゴリ不明</li>
						</ul>
					</nav>
					<h1>カテゴリが見つかりません</h1>
					<p>指定されたカテゴリは存在しないか、削除されました。</p>
					<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
					eof;
				} else {
					$returnTITLE = "カテゴリ「{$category["name"]}」";
					$returnHTML .= <<<eof
					<nav aria-label="breadcrumb">
						<ul class="breadcrumb">
							<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
							<li>{$category["name"]}</li>
						</ul>
					</nav>
					<h1>カテゴリ「{$category["name"]}」</h1>
					eof;
					$perPage = $Blog["post_per_page"];
					$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
					$offset = ($page - 1) * $perPage;
					$countstmt = $db->prepare("SELECT COUNT(*) FROM article WHERE category = :id");
					$countstmt->bindValue(":id",$id);
					$countstmt->execute();
					$row = $countstmt->fetch(PDO::FETCH_NUM);
					$totalArticles = (int)$row[0];
					$totalPages = ceil($totalArticles / $perPage);

					$stmt = $db->prepare("SELECT * FROM article WHERE category = :id ORDER BY id DESC LIMIT :limit OFFSET :offset");
					$stmt->bindValue(":id",$id);
					$stmt->bindValue(":limit",$perPage,PDO::PARAM_INT);
					$stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
					$stmt->execute();
					if($totalArticles > 0) {
						$returnHTML .= "<p>{$totalArticles}件の記事が見つかりました。</p>";
					} else {
						$returnHTML .= "<p>記事が見つかりませんでした。</p>";
					}
					while($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$url = htmlspecialchars($article["custom_url"],ENT_QUOTES,"UTF-8");
						$title = htmlspecialchars($article["title"],ENT_QUOTES,"UTF-8");
						$content = preg_replace("/<[^>]*>/"," ",$article["content"]);
						$content = preg_replace("/\s+/"," ",$content);
						$content = trim($content);
						if(mb_strlen($content) > $limit) {
							$content = mb_substr($content,0,$limit)."…";
						}
						isset($article["img"]) ? $imgSrc = "{$ps}data/img/{$article["img"]}" : $imgSrc = "{$ps}data/system/no-image.png";
						$returnHTML .= <<<eof
						<div class="article-card">
							<div class="card-image">
								<img src="$imgSrc" alt="記事のタイトル">
							</div>
							<div class="card-content">
								<h3 class="card-title">$title</h3>
								<p class="card-excerpt">$content</p>
								<a href="{$ps}article/$url" class="read-more">続きを読む</a>
							</div>
						</div>
						eof;
					}
					$back = $page - 1;
					$next = $page + 1;
					$returnHTML .= '<div class="pagination">';
					if($page > 1) {
						$returnHTML .= "<a href=\"?page=$back\">≪</a>";
					}
					for($i = 1;$i <= $totalPages;$i++) {
						if($i === $page) {
							$returnHTML .= "<a href=\"?page=$i\" class=\"active\">$i</a>";
						} else {
							$returnHTML .= "<a href=\"?page=$i\">$i</a>";
						}
					}
					if($page < $totalPages) {
						$returnHTML .= "<a href=\"?page=$next\">≫</a>";
					}
					$returnHTML .= "</div>";
				}
			} else {
				header("$protocol 404 Not Found");
				$returnTITLE = "カテゴリ不明";
				$returnHTML .= <<<eof
				<nav aria-label="breadcrumb">
					<ul class="breadcrumb">
						<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
						<li>カテゴリ不明</li>
					</ul>
				</nav>
				<h1>カテゴリIDが指定されてません</h1>
				<p>カテゴリのIDが指定されていません。</p>
				<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
				eof;
			}
			break;
		case "author":
			$id = (int)$id;
			if(!empty($id)) {
				$stmt = $db->prepare("SELECT name FROM users WHERE id = :id");
				$stmt->bindValue(":id",$id);
				$stmt->execute();
				$author = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!empty($author)) {
					$returnTITLE = "編集者「{$author["name"]}」";
					$returnHTML .= <<<eof
					<nav aria-label="breadcrumb">
						<ul class="breadcrumb">
							<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
							<li>{$author["name"]}</li>
						</ul>
					</nav>
					<h1>編集者「{$author["name"]}」</h1>
					eof;
					$perPage = $Blog["post_per_page"];
					$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
					$offset = ($page - 1) * $perPage;
					$countstmt = $db->prepare("SELECT COUNT(*) FROM article WHERE users = :id");
					$countstmt->bindValue(":id",$id);
					$countstmt->execute();
					$row = $countstmt->fetch(PDO::FETCH_NUM);
					$totalArticles = (int)$row[0];
					$totalPages = ceil($totalArticles / $perPage);
					
					$stmt = $db->prepare("SELECT * FROM article WHERE users = :id ORDER BY id DESC LIMIT :limit OFFSET :offset");
					$stmt->bindValue(":id",$id);
					$stmt->bindValue(":limit",$perPage,PDO::PARAM_INT);
					$stmt->bindValue(":offset",$offset,PDO::PARAM_INT);
					$stmt->execute();
					if($totalArticles > 0) {
						$returnHTML .= "<p>{$totalArticles}件の記事が見つかりました。</p>";
					} else {
						$returnHTML .= "<p>記事が見つかりませんでした。</p>";
					}
					while($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$url = htmlspecialchars($article["custom_url"],ENT_QUOTES,"UTF-8");
						$title = htmlspecialchars($article["title"],ENT_QUOTES,"UTF-8");
						$content = preg_replace("/<[^>]*>/"," ",$article["content"]);
						$content = preg_replace("/\s+/"," ",$content);
						$content = trim($content);
						if(mb_strlen($content) > $limit) {
							$content = mb_substr($content,0,$limit)."…";
						}
						isset($article["img"]) ? $imgSrc = "{$ps}data/img/{$article["img"]}" : $imgSrc = "{$ps}data/system/no-image.png";
						$returnHTML .= <<<eof
						<div class="article-card">
							<div class="card-image">
								<img src="$imgSrc" alt="記事のタイトル">
							</div>
							<div class="card-content">
								<h3 class="card-title">$title</h3>
								<p class="card-excerpt">$content</p>
								<a href="{$ps}article/$url" class="read-more">続きを読む</a>
							</div>
						</div>
						eof;
					}
					$back = $page - 1;
					$next = $page + 1;
					$returnHTML .= <<<eof
					<div class="pagination">
					eof;
					if($page > 1) {
						$returnHTML .= <<<eof
						<a href="?page=$back">≪</a>
						eof;
					}
					for($i = 1;$i <= $totalPages;$i++) {
						if($i === $page) {
							$returnHTML .= <<<eof
							<a href="?page=$i" class="active">$i</a>
							eof;
						} else {
							$returnHTML .= <<<eof
							<a href="?page=$i">$i</a>
							eof;
						}
					}
					if($page < $totalPages) {
						$returnHTML .= <<<eof
						<a href="?page=$next">≫</a>
						eof;
					}
					$returnHTML .= "</div>";
				} else {
					header("$protocol 404 Not Found");
					$returnTITLE = "編集者不明";
					$returnHTML .= <<<eof
					<nav aria-label="breadcrumb">
						<ul class="breadcrumb">
							<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
							<li>編集不明</li>
						</ul>
					</nav>
					<h1>編集者が見つかりません</h1>
					<p>指定されたアカウントIDの記事は存在しないか、削除されました。</p>
					<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
					eof;
				}
			} else {
				header("$protocol 404 Not Found");
				$returnTITLE = "編集者不明";
				$returnHTML .= <<<eof
				<nav aria-label="breadcrumb">
					<ul class="breadcrumb">
						<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
						<li>編集者不明</li>
					</ul>
				</nav>
				<h1>アカウントIDが指定されてません</h1>
				<p>アカウントIDが指定されていません。</p>
				<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
				eof;
			}
			break;
		case "article":
			$articles = [];
			if(!empty($id)) {
				$stmt = $db->prepare("SELECT * FROM article WHERE custom_url = :custom_url");
				$stmt->bindValue(":custom_url",$id);
				$stmt->execute();
				while($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$articles[] = $result;
				}
				if(!empty($articles)) {
					foreach($articles as $article) {
						$title = htmlspecialchars($article["title"],ENT_QUOTES,"UTF-8");
						$returnTITLE = "記事「{$title}」";

						$stmt1 = $db->prepare("SELECT id,name FROM category WHERE id = :categoryId");
						$stmt1->bindValue(":categoryId",(int)$article["category"]);
						$stmt1->execute();
						$category = $stmt1->fetch(PDO::FETCH_ASSOC);

						$stmt2 = $db->prepare("SELECT id,name FROM users WHERE id = :userId");
						$stmt2->bindValue(":userId",(int)$article["users"]);
						$stmt2->execute();
						$users = $stmt2->fetch(PDO::FETCH_ASSOC);

						$cateName = htmlspecialchars($category["name"],ENT_QUOTES,"UTF-8");
						$content = purify_html($article["content"]);
						isset($article["img"]) ? $imgSrc = "{$ps}data/img/{$article["img"]}" : $imgSrc = "{$ps}data/system/no-image.png";

						include_once("src/block_plugin.php");

						if($article["created_at"] === $article["updated_at"]) {
							$time = date("Y年n月j日",strtotime($article["created_at"]));
						} else {
							$time = date("Y年n月j日",strtotime($article["created_at"]))."（".date("Y年n月j日",strtotime($article["updated_at"]))."）";
						}
						$time = htmlspecialchars($time,ENT_QUOTES,"UTF-8");
						$returnHTML .= <<<eof
						<nav aria-label="breadcrumb">
							<ul class="breadcrumb">
								<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
								<li><a href="{$ps}category/{$category["id"]}">{$cateName}</a> <i class="bi bi-caret-right-fill"></i></li>
								<li>{$title}</li>
							</ul>
						</nav>
						<h1>{$title}</h1>
						<p><i class="bi bi-clock-fill"></i> $time<br><i class="bi bi-folder-fill"></i> {$cateName}<br><i class="bi bi-person-circle"> <a href="{$ps}author/{$users["id"]}">{$users["name"]}</a></i></p>
						<p><img src="$imgSrc" class="thumbnail"></p>
						<article>$content</article>
						eof;
					}
				} else {
					header("$protocol 404 Not Found");
					$returnTITLE = "記事不明";
					$returnHTML .= <<<eof
					<nav aria-label="breadcrumb">
						<ul class="breadcrumb">
							<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
							<li>記事不明</li>
						</ul>
					</nav>
					<h1>記事が見つかりません</h1>
					<p>指定された記事は存在しないか、削除されました。</p>
					<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
					eof;
				}
			} else {
				header("$protocol 404 Not Found");
				$returnTITLE = "記事不明";
				$returnHTML .= <<<eof
				<nav aria-label="breadcrumb">
					<ul class="breadcrumb">
						<li><a href="{$ps}"><i class="bi bi-house"></i> ホーム</a> <i class="bi bi-caret-right-fill"></i></li>
						<li>記事不明</li>
					</ul>
				</nav>
				<h1>記事IDが指定されてません</h1>
				<p>記事のID または タイトルが指定されていません。</p>
				<p><a class="btn primary" href="{$ps}">≪ ホームへ戻る</a></p>
				eof;
			}
			break;
		default:
			$returnHTML = $returnTITLE = null;
	}
	return [
		"title" => $returnTITLE,
		"main" => $returnHTML,
	];
}
function purify_html($html) {
	require_once("htmlpurifier/library/HTMLPurifier.auto.php");
	$config = HTMLPurifier_Config::createDefault();
	$config->set("Core.Encoding","UTF-8");
	$config->set("HTML.Doctype","HTML 4.01 Transitional");
	$config->set("Cache.DefinitionImpl",null);
	$config->set("HTML.Nofollow",true);
	$config->set("HTML.Allowed",
		"p,br,a[href|title|target],strong,em,ul,ol,li,blockquote,code,pre,".
		"img[src|alt|title|width|height],".
		"h1,h2,h3,h4,h5,h6,".
		"table,thead,tbody,tr,th,td,span,div,".
		"section,article,header,footer,nav,aside,figure,figcaption,main,time[datetime],".
		"details[open],summary,".
		"audio[src|controls|autoplay|loop|muted|preload|crossorigin],".
		"video[src|controls|autoplay|loop|muted|poster|preload|width|height|crossorigin],".
		"source[src|type]"
	);
	$def = $config->getHTMLDefinition(true);
	$def->addElement("section","Block","Flow","Common");
	$def->addElement("article","Block","Flow","Common");
	$def->addElement("header","Block","Flow","Common");
	$def->addElement("footer","Block","Flow","Common");
	$def->addElement("nav","Block","Flow","Common");
	$def->addElement("aside","Block","Flow","Common");
	$def->addElement("figure","Block","Flow","Common");
	$def->addElement("figcaption","Block","Flow","Common");
	$def->addElement("main","Block","Flow","Common");
	$def->addElement("time","Inline","Flow","Common",["datetime" => "CDATA"]);
	$def->addElement("details","Block","Flow","Common",["open" => "Bool"]);
	$def->addElement("summary","Block","Flow","Common");
	$def->addElement("audio", "Block", "Flow", "Common",[
		"src" => "URI",
		"controls" => "Bool",
		"autoplay" => "Bool",
		"loop" => "Bool",
		"muted" => "Bool",
		"preload" => "Enum#auto,metadata,none",
		"crossorigin" => "Enum#anonymous,use-credentials",
	]);
	$def->addElement("video", "Block", "Flow", "Common",[
		"src" => "URI",
		"controls" => "Bool",
		"autoplay" => "Bool",
		"loop" => "Bool",
		"muted" => "Bool",
		"poster" => "URI",
		"preload" => "Enum#auto,metadata,none",
		"width" => "Length#px",
		"height" => "Length#px",
		"crossorigin" => "Enum#anonymous,use-credentials",
	]);
	$def->addElement("source", "Block", "Flow", "Common",[
		"src" => "URI",
		"type" => "Text",
	]);
	$purifier = new HTMLPurifier($config);
	return $purifier->purify($html);
}
function BlitZlog_rrmdir($dir) {
	if(!is_dir($dir)) return true;
	$objects = scandir($dir);
	foreach($objects as $object) {
		if($object != "." && $object != "..") {
			if(is_dir($dir.DIRECTORY_SEPARATOR.$object) && !is_link($dir.DIRECTORY_SEPARATOR.$object)) {
				BlitZlog_rrmdir($dir. DIRECTORY_SEPARATOR .$object);
			} else {
				unlink($dir. DIRECTORY_SEPARATOR .$object);
			}
		}
	}
	return rmdir($dir);
}
function sanitizeForHtml($data) {
	$sanitized = [];
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$sanitized[$key] = sanitizeForHtml($value);
		} else {
			$sanitized[$key] = htmlspecialchars($value,ENT_QUOTES,"UTF-8");
		}
	}
	return $sanitized;
}
function moduleSetting($mode,$name,$value = null) {
	$setting_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."module".DIRECTORY_SEPARATOR."__setting";
	$setting_file_path = $setting_dir.DIRECTORY_SEPARATOR.basename($name).".set";

	switch($mode) {
		case "set":
			if($value === null) {
				return false;
			}
			if(!is_dir($setting_dir)) {
				if(!mkdir($setting_dir,0755,true)) {
					error_log("Error: Failed to create module setting directory: ".$setting_dir);
					return false;
				}
			}
			$value = sanitizeForHtml($value);
			$PHPcode = "<?php\n";
			foreach ($value as $key => $val) {
				$PHPcode .= "$".$key." = ".var_export($val,true).";\n";
			}
			if(file_put_contents($setting_file_path,$PHPcode) === false) {
				error_log("Error: Failed to write to module setting file: ".$setting_file_path);
				return false;
			}
			return true;
		case "get":
			if(!file_exists($setting_file_path)) {
				return false;
			}
			$settings = [];
			$temp_vars = (function() use($setting_file_path) {
				include $setting_file_path;
				return get_defined_vars();
			})();
			foreach($temp_vars as $key => $val) {
				if($key !== "setting_file_path") {
					$settings[$key] = $val;
				}
			}
			return $settings;
		default:
			return false;
	}
}