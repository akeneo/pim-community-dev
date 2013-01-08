$(document).ready(function () {
    /* create overlay for popups */
    $('<div id="bar-drop-overlay"></div>').appendTo('body');
    /* dinamic height for central column */
    function changeHeight() {
        var _chWindowHeight = $(window).height();
        var _chMyHeight = _chWindowHeight - $("header").outerHeight() - $("footer").outerHeight() - 3;
        $('div.layout-content').innerHeight(_chMyHeight);
    };
    /* init first time*/
    changeHeight();
    /* init when resize window */
    $(window).resize(function() {
        changeHeight();
    });

    /* side bar functionality */
    $('div.side-nav').each(function () {
        var myParent = $(this);
        /* open close bar */
        $(this).find("span.maximaze-bar").click(function () {
            if (($(myParent).hasClass("side-nav-open")) || ($(myParent).hasClass("side-nav-locked"))) {
                $(myParent).removeClass("side-nav-locked side-nav-open");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').removeClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').removeClass('right-locked');
                }
                $(myParent).find('.bar-tools').css({
                    "height": "auto",
                    "overflow" : "visible"
                })
            } else {
                $(myParent).addClass("side-nav-open");
                var openBarHeight = $("div.page-container").height() - 20;
                /* minus top-padding and bottom-padding */
                $(myParent).height(openBarHeight);
                var testBarScroll = $(myParent).find('.bar-tools').height();
                if(openBarHeight < testBarScroll ){
                    $(myParent).find('.bar-tools').height((openBarHeight - 20)).css({
                        "overflow" : "auto"
                    })
                }
            }
        });

        /* lock&unlock bar */
        $(this).find("span.lock-bar").click(function () {
            if ($(this).hasClass("lock-bar-locked")) {
                $(myParent).addClass("side-nav-open")
                    .removeClass("side-nav-locked");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').removeClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').removeClass('right-locked');
                }
            } else {
                $(myParent).addClass("side-nav-locked")
                    .removeClass("side-nav-open");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').addClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').addClass('right-locked');
                }

            }
            $(this).toggleClass('lock-bar-locked');
        });

        /* open&close popup for bar items when bar is minimized. */
        $(this).find('.bar-tools li').each(function () {
            var myItem = $(this);
            $(myItem).find('.sn-opener').click(function () {
                $(myItem).find("div.nav-box").fadeToggle("slow");
                var overlayHeight = $('#page').height();
                var overlayWidth = $('#page > .wrapper').width();
                $('#bar-drop-overlay').width(overlayWidth).height(overlayHeight);
                $('#bar-drop-overlay').toggleClass('bar-open-overlay');
            });
            $(myItem).find("span.close").click(function () {
                $(myItem).find("div.nav-box").fadeToggle("slow");
                $('#bar-drop-overlay').toggleClass('bar-open-overlay');
            });
            $('#bar-drop-overlay').on({
                click:function () {
                    $(myItem).find("div.nav-box").animate({
                        opacity:0,
                        display:'none'
                    }, function () {
                        $(this).css({
                            opacity:1,
                            display:'none'
                        })
                    });
                    $('#bar-drop-overlay').removeClass('bar-open-overlay');
                }
            });
        });
    })
});