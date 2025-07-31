// $(document).on("click",`a[href^="article/"], a[href^="category/"], a[href="./?page="], a[href^="search/"]`,function(e) {
//     e.preventDefault();

//     const url = $(this).attr("href");
//     const path = url.replace(/\/+$/, "");

//     loadContent(path);
// });
// function loadContent(path) {
//     $.ajax({
//         url: path,
//         type: "GET",
//         dataType: "html",
//     }).done((response) => {
//         history.pushState(null, "", path);
//         $("html").html(response);
//         // // レスポンスのHTML文字列を新しいDOMにパースするための仮想要素を作成
//         // const $tempDiv = $("<div>").html(response); // jQueryオブジェクトとしてHTMLをパース

//         // // .content 要素を子孫から探す
//         // const $newContentElement = $tempDiv.find(".content").first();

//         // if ($newContentElement.length > 0) {
//         //     // .content 要素が見つかった場合
//         //     const newContentHtml = $newContentElement.html();
//         //     $(".content").html(newContentHtml);

//         //     // URLと履歴を更新
//         //     history.pushState(null, "", path);

//         //     // ページタイトルを更新 (head -> title の子孫要素を探す)
//         //     const newTitle = $tempDiv.find("head title").text();
//         //     console.log(newTitle);
//         //     if (newTitle) {
//         //         document.title = newTitle;
//         //     } else {
//         //         // タイトルが見つからない場合のフォールバック
//         //         document.title = "BlitZlog";
//         //     }

//         //     // ページトップにスクロール
//         //     window.scrollTo(0, 0);

//         //     // TODO: Ajaxで読み込まれたコンテンツ内の動的な要素を再初期化
//         //     // main.blitzlog.jsのUI初期化関数を呼び出す
//         //     if (typeof reinitializeUI === 'function') {
//         //         reinitializeUI();
//         //     }

//         // } else {
//         //     // .content 要素が見つからなかった場合のエラーハンドリング
//         //     $(".content").html(`<div class="error">コンテンツの読み込みに失敗しました。<br>コンテンツ要素（.content）が見つかりませんでした。</div>`);
//         //     console.error("Error: .content element not found in Ajax response.");
//         //     console.log("Full Ajax response:", response); // デバッグ用にレスポンス全体を出力
//         // }
//     }).fail((xhr,status,error) => {
//         $(".content").html(`<div class="error">コンテンツの読み込みに失敗しました。</div>`);
//         console.error("Ajax error:", status, error);
//     });
// }
// $(window).on("popstate",function() {
//     const path = window.location.pathname;
//     if(path !== "/") {
//         loadContent(path);
//     } else {
//         // loadContent("/");
//     }
// });