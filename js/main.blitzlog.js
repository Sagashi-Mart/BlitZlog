$(document).ready(function() {
    $(".submenu").hide(); // サブメニューを非表示に初期化
    let lastClickedItem = null;
  
    // 親メニュークリック時の処理
    $(".has-submenu > a").on("click", function(event) {
        const $submenu = $(this).siblings(".submenu");
        
        if(lastClickedItem === this) {
            return true; // 2回目のクリックでリンク移動
        }
        
        event.preventDefault(); // デフォルト動作を無効化
        $(".submenu").not($submenu).hide(); // 他のサブメニューを閉じる
        $submenu.toggle(); // 現在のサブメニューを表示/非表示
        lastClickedItem = this; // クリック状態を保存
    });
  
    // メニュー外クリック時の処理
    $(document).on("click", function (event) {
        if(!$(event.target).closest(".has-submenu").length) {
            $(".submenu").hide(); // サブメニューを全て非表示
            lastClickedItem = null; // フラグをリセット
        }
    });
  
    // マウス移動時の処理を追加
    $(".menu-list > li").on("mouseleave", function() {
        $(this).find(".submenu").hide(); // サブメニューを閉じる
        lastClickedItem = null;
    });
  });
  $(document).ready(function () {
    // メニューを開く
    $(".menu-toggle").on("click", function () {
      $("#offcanvasMenu").addClass("active");
    });
  
    // メニューを閉じる
    $(".menu-close").on("click", function () {
      $("#offcanvasMenu").removeClass("active");
    });
  
    // メニュー外をクリックした際に閉じる
    $(document).on("click", function (event) {
      if (!$(event.target).closest("#offcanvasMenu, .menu-toggle").length) {
        $("#offcanvasMenu").removeClass("active");
      }
    });
  });
  document.addEventListener("DOMContentLoaded", function () {
    const content = document.querySelector(".content");
    const footer = document.querySelector(".site-footer");
    const header = document.querySelector("header");
  
    if (content.offsetHeight + footer.offsetHeight + header.offsetHeight + 50 < window.innerHeight) {
      footer.style.position = "absolute";
      footer.style.bottom = "0";
      footer.style.width = "100%";
    } else {
      footer.style.position = "static";
    }
  });
    