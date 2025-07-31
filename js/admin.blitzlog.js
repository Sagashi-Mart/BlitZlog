/*
BlitZlog v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
let prevFunc = null;
let nowFunc = null;
let currentPage = 1;
const ARTICLES_PER_PAGE = 10;

const routes = {
    "home": mainAdminHome,
    "login": login,
    "article": (param,id) => mainArticle(param,id),
    "category": (param,id) => mainCategory(param,id),
    "file": (param,id) => mainFile(param,id),
    "plugins": (param,id) => mainPlugins(param,id),
    "store": (param,id) => mainStore(param,id),
    "news": mainNews,
    "analyze": (param,id) => mainAnalyze(param,id),
    "system": (param,id) => mainSystem(param,id),
    "setting": mainSetting,
};

$(function() {
    mainDis();
    $(".content").on("click",`[data-func]`,function() {
        mainDis(true);
        const startTime = Date.now();
        const funcName = $(this).data("func");
        const funcOption = $(this).data("func-option");

        if(typeof routes[funcName] === "function") {
            prevFunc = nowFunc;
            nowFunc = funcName;
            console.log(nowFunc,prevFunc);
            if(funcOption) {
                const params = funcOption.split(",");
                routes[funcName](...params);
            } else {
                routes[funcName]();
            }
        } else {
            console.warn(`Function ${funcName} is not defined.`);
        }
        adjustMenuHeight();
    });
});
function mainDis(mode = false) {
    $.ajax({
        url: `${PS}api/admin/login.php`,
        data: {
            check: true
        },
        type: "POST",
        dataType: "json"
    }).done((data) => {
        if(data.message === true) {
            if(mode === true) return;
            const pathname = new URLSearchParams(document.location.search);
            const routeName = BLOG_PATH;
            const routeParam = pathname.get("mode");
            const routeID = pathname.get("id");

            if(routes[routeName] && typeof routes[routeName] === "function") {
                routes[routeName](routeParam,routeID);
            } else {
                $(".content").html(`<h1>404 Not Found</h1>
                <p>お探しのページは見つかりませんでした。<br>URLを間違えている可能性があります。</p>
                <p><a class="btn primary" data-func="home">≪ ホームへ戻る</a></p>`);
            }
        } else {
            login();
        }
    });
}
function mainAdminHome() {
    history.pushState(null,"",`${PS}admin/home`);
    document.title = `${BLOG_TITLE} - ホーム`;

    $(".content").html(`<h1><i class="bi bi-house"></i> ホーム</h1>
    <p>BlitZlogの管理画面へようこそ。</p>
    <div class="article-card" data-func="article">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-newspaper"></i> 記事</h3>
            <p class="card-excerpt">作成、変更、削除</p>
        </div>
    </div>
    <div class="article-card" data-func="category">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-tags"></i> カテゴリ</h3>
            <p class="card-excerpt">作成、変更、削除</p>
        </div>
    </div>
    <div class="article-card" data-func="file">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-files"></i> ファイル</h3>
            <p class="card-excerpt">アップロード、削除</p>
        </div>
    </div>
    <div class="article-card" data-func="plugins">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-plugin"></i> モジュール・テーマ</h3>
            <p class="card-excerpt">モジュール、ブロック、テーマの管理</p>
        </div>
    </div>
    <div class="article-card" data-func="store">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-bag"></i> ストア</h3>
            <p class="card-excerpt">BlitZlog Store</p>
        </div>
    </div>
    <div class="article-card" data-func="news">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-info-circle"></i> お知らせ</h3>
            <p class="card-excerpt">公式からのニュース、このBlitZlogから</p>
        </div>
    </div>
    <div class="article-card" data-func="info">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-graph-up"></i> アクセス解析</h3>
            <p class="card-excerpt">アクセス数、ログインなど</p>
        </div>
    </div>
    <div class="article-card" data-func="system">
        <div class="card-content">
            <h3 class="card-title"><i class="bi bi-credit-card-2-front"></i> システム</h3>
            <p class="card-excerpt">BlitZlogのシステム管理</p>
        </div>
    </div>`);
}
function mainArticle(mode = null,id = null) {
    const contentArea = $(".content");
    if(mode == "create") {
        history.pushState(null,"",`${PS}admin/article?mode=create`);
        document.title = `${BLOG_TITLE} - 記事作成`;
        articleInit(mode);
        tinymceInit();
    } else if(mode == "edit") {
        history.pushState(null,"",`${PS}admin/article?mode=edit&id=${id}`);
        document.title = `${BLOG_TITLE} - 記事編集`;
        articleInit(mode,id);
        $.ajax({
            url: `${PS}api/admin/article.php`,
            type: "POST",
            dataType: "json",
            data: {
                read: id
            }
        }).done((data) => {
            document.title = `${BLOG_TITLE} - 記事編集「${data.message.title}」`;
            $(`[data-input-article="title"]`).val(data.message.title);
            $(`[data-input-article="category"]`).val(`${data.message.category.id}`);
            if(empty(data.message.status)) {
                $(`[data-input-article="status"]`).val(0);
            } else {
                $(`[data-input-article="status"]`).val(data.message.status);
            }
            $(`[data-input-article="url"]`).val(data.message.custom_url);
            $(`[data-input-article="date"]`).val(data.message.updated_at);
            $(`[data-input-article="content"]`).val(data.message.content);
        });
        tinymceInit();
    } else if(mode == "remove") {
        // 
    } else {
        history.pushState(null,"",`${PS}admin/article?id=${currentPage}`);
        document.title = `${BLOG_TITLE} - 記事一覧`;

        contentArea.html(`
            <h1><i class="bi bi-newspaper"></i> 記事</h1>
            <a data-func="home" class="btn secondary">≪ 戻る</a>
            <button class="btn primary" data-func="article" data-func-option="create">新規記事作成</button>
            <div data-action="article-list-area">
                <p>記事データを読み込み中・・・</p>
                <div class="loading-spinner"></div>
            </div>
            <div class="pagination-controls" data-pagination-area></div>`);
        $.ajax({
            url: `${PS}api/admin/article.php`,
            type: "POST",
            dataType: "json",
            data: {
                list: true
            }
        }).done((data) => {
            if(data.code !== 200) {
                $(`[data-action="article-list-area"]`).html(`<div class="alert danger">記事の読み込みに失敗しました: ${data.message}</div>`);
                $(`[data-pagination-area]`).empty();
                return;
            }

            const allArticles = data.message.reverse();
            const totalArticles = allArticles.length;
            const totalPages = Math.ceil(totalArticles / ARTICLES_PER_PAGE);

            const startIndex = (currentPage - 1) * ARTICLES_PER_PAGE;
            const endIndex = startIndex + ARTICLES_PER_PAGE;
            const articlesToDisplay = allArticles.slice(startIndex,endIndex);

            let articlesHtml = "";
            if(articlesToDisplay.length === 0) {
                articlesHtml = "<p>まだ記事がありません。</p>";
            } else {
                articlesToDisplay.forEach(article => {
                    if(article.status === 0 || article.status === 1 || article.status === null) {
                        articlesHtml += `<div class="admin-card">
                            <div class="card-content">
                                <h3 class="card-title">${article.title}</h3>
                                <p class="card-excerpt">
                                    <i class="bi bi-clock"></i> ${article.created_at} / ${article.updated_at}　
                                    ${isset(article.reserved_at) ? `<i class="bi bi-clock-history"></i> ${article.reserved_at}` : ``}
                                </p>
                                <a data-func="article" data-func-option="edit,${article.id}" class="btn success">編集</a>　
                                <a data-func="article" data-func-option="remove,${article.id}" class="btn danger">削除</a>　
                            </div>
                        </div>`;
                    }
                });
            }
            $(`[data-action="article-list-area"]`).html(articlesHtml);
            renderPaginationControls(totalPages,currentPage);
        }).fail((jqXHR) => {
            if(jqXHR.status === 401) {
                $(`.content`).html(`<div class="alert danger">ログインが必要です。</div>`);
            }
        });
    }
}
function mainCategory(mode = null,id = null) {}
function mainFile(mode = null,id = null) {}
function mainPlugins(mode = null,id = null) {}
function mainStore(mode = null,id = null) {}
function mainAnalyze(mode = null,id = null) {}
function mainSystem(mode = null,id = null) {}
function mainNews() {}
function mainSetting() {}
function login() {
    history.pushState(null,"",`${PS}admin/login`);
    document.title = `${BLOG_TITLE} - ログイン`;
    $(".content").html(`<h1>ログイン</h1>
    <a data-func="home" class="btn secondary">≪ 戻る</a>
    <div data-action="alert"></div>
    <p>以下の項目にユーザー情報を入力してください。</p>
    <div>
        <p>メールアドレス</p>
        <input type="text" data-input-login="email" placeholder="ここに入力してください">
        <p>パスワード</p>
        <input type="password" data-input-login="pswd" placeholder="ここに入力してください">
        <p></p><br>
        <button class="btn success" data-btn-id="login">ログイン</button>
    </div>`);

    $(`[data-btn-id="login"]`).on("click",() => {
        const alert = $(`[data-action="alert"]`);
        $.ajax({
            url: `${PS}api/admin/login.php`,
            type: "POST",
            data: {
                email: $(`[data-input-login="email"]`).val(),
                pswd: $(`[data-input-login="pswd"]`).val(),
            },
            dataType: "json"
        }).done((data) => {
            const message = data.message;
            switch(message) {
                case "Already logged in.":
                    alert.html(`<div class="alert info">既にログイン済みです。</div>`);
                    break;
                case "Email address or password not entered.":
                    alert.html(`<div class="alert warning">メールアドレスまたはパスワードが入力されていません。</div>`);
                    break;
                case "Incorrect email address or password.":
                    alert.html(`<div class="alert danger">メールアドレスまたはパスワードが違います。</div>`);
                    break;
                case "I can't login to BlitZlog.":
                    alert.html(`<div class="alert purple">BlitZlogにログインできません。異常が発生している可能性があります。</div>`);
                    break;
                case "BlitZlog login has been blocked.":
                    alert.html(`<div class="alert purple">BlitZlogのログインがブロックされています。異常が発生している可能性があります。</div>`);
                    break;
                case "Failed to retrieve from database.":
                    alert.html(`<div class="alert secondary">データベースにアクセスできません。</div>`);
                    break;
                case "BlitZlog login successful.":
                    mainAdminHome();
                    break;
                default:
                    alert.html(`<div class="alert secondary">問題が発生しました。再試行しても変わらない場合は、異常が発生している可能性があります。</div>`);
            }
        }).fail((jqXHR) => {
            alert.html(`<div class="alert secondary">問題が発生しました。再試行しても変わらない場合は、異常が発生している可能性があります。</div>`);
        });
    });
}
// その他の関数
function empty(e) {
    if(e === undefined || e === null || e === "") {
        return true;
    }
    return false;
}
function isset(e) {
    if(e === undefined || e === null) {
        return false;
    }
    return true;
}
function openImageFolder(e) {
}
function articleInit(mode,articleId = null) {
    const modeTitle = mode === "create" ? "作成" : mode === "edit" ? "編集" : "";
    const contentArea = $(".content");
    contentArea.html(`<h1><i class="bi bi-newspaper"></i> 記事${modeTitle}</h1>
    <a data-func="article" class="btn secondary">≪ 戻る</a>
    <div data-action="alert"></div>
    <p>以下の項目に記事の情報を入力してください。</p>
    <div>
        <p><b>タイトル</b></p>
        <input type="text" data-input-article="title" placeholder="ここに記事のタイトルを入力してください">
        <p><b>カテゴリ</b></p>
        <select data-input-article="category">
            <option value="" disabled selected>選択してください</option>
        </select>
        <p><b>サムネイル</b></p>
        </p><label onclick="openImageFolder(this)" class="btn secondary" data-input-article="img" value="" for="popupFlag1">画像フォルダを開く</label></p>
        <details>
            <summary>詳細情報</summary>
            <p><b>公開状態</b></p>
            <select data-input-article="status">
                <option value="0">公開</option>
                <option value="1">非公開</option>
            </select>
            <p><b>公開日時</b></p>
            <input type="datetime-local" data-input-article="date" placeholder="公開する場合は日時を指定してください">
            <p><b>カスタムURL</b></p>
            <input type="text" data-input-article="url" placeholder="カスタムするURLを入力してください">
        </details>
        <p><b>内容</b></p>
        <textarea data-input-article="content" placeholder="ここに記事の内容を入力してください"></textarea>
        <p></p>
        <button class="btn success" data-btn-id="article-edit">記事を更新</button>
    </div>`);
    $.ajax({
        url: `${PS}api/admin/category.php`,
        type: "POST",
        dataType: "json",
        data: {
            list: true
        }
    }).done((data) => {
        if(data.code !== 200) {
            $(`[data-action="alert"]`).html(`<div class="alert danger">カテゴリの読み込みに失敗しました: ${data.message}</div>`);
            return;
        }
        const categories = data.message;
        const categorySelect = $(`[data-input-article="category"]`);
        categories.forEach(category => {
            categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
        });
    }).fail((jqXHR) => {
        $(`[data-action="alert"]`).html(`<div class="alert danger">カテゴリの読み込みに失敗しました: ${jqXHR.statusText}</div>`);
    });
    $(`[data-btn-id="article-edit"]`).on("click",function() {
        $(`[data-btn-id="article-edit"]`).prop("disabled",true);
        $(`[data-action="alert"]`).html(`<div class="alert info">記事を保存しています・・・</div>`);
        const articlePayload = {
            title: $(`[data-input-article="title"]`).val(),
            category: $(`[data-input-article="category"]`).val(),
            img: $(`[data-input-article="img"]`).val(),
            status: $(`[data-input-article="status"]`).val(),
            date: $(`[data-input-article="date"]`).val(),
            url: $(`[data-input-article="url"]`).val(),
            content: $(`[data-input-article="content"]`).val(),
            id: articleId
        };
        switch(mode) {
            case "create":
                articlePayload.create = true;
                break;
            case "edit":
                articlePayload.edit = true;
                break;
        }
        $.ajax({
            url: `${PS}api/admin/article.php`,
            type: "POST",
            dataType: "json",
            data: articlePayload
        }).done((data) => {
            if(data.code === 200 &&data.message == `${mode} success.`) {
                mainArticle();
            } else {
                $(`[data-btn-id="article-edit"]`).prop("disabled",false);
                $(`[data-action="alert"]`).html(`<div class="alert danger">記事の保存中にエラーが発生しました。</div>`);
            }
        }).fail((jqXHR) => {
            $(`[data-btn-id="article-edit"]`).prop("disabled",false);
            $(`[data-action="alert"]`).html(`<div class="alert secondary">問題が発生しました。再試行しても変わらない場合は、異常が発生している可能性があります。</div>`);
        });
    });
}
function renderPaginationControls(totalPages,currentPage) {
    const paginationArea = $(`[data-pagination-area]`);
    paginationArea.empty();

    if(totalPages <= 1) {
        return;
    }

    let paginationHtml = `<ul class="pagination">`;

    paginationHtml += `<li class="page-item ${currentPage === 1 ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="bi bi-caret-left"></i></a>
    </li>`;

    for(let i = 1; i <= totalPages; i++) {
        paginationHtml += `<li class="page-item ${i === currentPage ? "active" : ""}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
    }

    paginationHtml += `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="bi bi-caret-right"></i></a>
    </li>`;

    paginationHtml += "</ul>";
    paginationArea.html(paginationHtml);
}
$(document).on("click",".pagination .page-link",function(e) {
    e.preventDefault();

    const newPage = parseInt($(this).data("page"));
    
    if(isNaN(newPage) || newPage < 1 || newPage === currentPage || 
        ($(this).parent().hasClass("disabled"))) {
        return;
    }

    currentPage = newPage;
    mainArticle();
});