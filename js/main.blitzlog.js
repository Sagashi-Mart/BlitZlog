/*
BlitZlog v0.4.0-beta
(c) 2025 yu-., Sagashi Mart.
*/
function pageload() {
    $(".submenu").hide();
    let lastClickedItem = null;
    $(".has-submenu > a").on("click",function(event) {
        const $submenu = $(this).siblings(".submenu");
        if(lastClickedItem === this) {
            return true;
        }
        event.preventDefault();
        $(".submenu").not($submenu).hide();
        $submenu.toggle();
        lastClickedItem = this;
    });
    $(document).on("click",function(event) {
        if(!$(event.target).closest(".has-submenu").length) {
            $(".submenu").hide();
            lastClickedItem = null;
        }
    });
    $(".menu-list > li").on("mouseleave",function() {
        $(this).find(".submenu").hide();
        lastClickedItem = null;
    });
    $(".menu-toggle").on("click",function() {
        $("#offcanvasMenu").addClass("active");
    });
    $(".menu-close").on("click",function() {
        $("#offcanvasMenu").removeClass("active");
    });
    $(document).on("click",function(event) {
        if(!$(event.target).closest("#offcanvasMenu, .menu-toggle").length) {
            $("#offcanvasMenu").removeClass("active");
        }
    });
};
function adjustMenuHeight() {
    // const content = document.querySelector(".content");
    // const footer = document.querySelector(".site-footer");
    // const header = document.querySelector("header");

    // if(!content || !footer || !header) {
    //     return;
    // }

    // if(content.offsetHeight + footer.offsetHeight + header.offsetHeight + 20 < window.innerHeight) {
    //     footer.style.position = "absolute";
    //     footer.style.bottom = "0";
    //     footer.style.width = "100%";
    // } else {
    //     footer.style.position = "static";
    // }
}
window.addEventListener("load",pageload);
window.addEventListener("load",adjustMenuHeight);
window.addEventListener("resize",adjustMenuHeight);