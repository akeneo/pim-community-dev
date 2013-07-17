$(document).ready(function () {
    initLayout();

    /* hide progress bar on page ready in case we don't need hash navigation request*/
    if ((typeof Oro.hashNavigationEnabled == "undefined") ||
        !Oro.hashNavigationEnabled() ||
        !Oro.Navigation.prototype.checkHashForUrl()) {
        if ($('#page-title').size()) {
            document.title = $('#page-title').text();
        }
        hideProgressBar();
    }

    /* side bar functionality */
    $('div.side-nav').each(function () {
        var myParent = $(this);
        var myParentHolder = $(myParent).parent().height() -18;
        $(myParent).height(myParentHolder);
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
        /* open content for open bar */
        $(myParent).find('ul.bar-tools > li').each(function(){
            var _barLi = $(this);
            $(_barLi).find('span.open-bar-item').click(function(){
                $(_barLi).find('div.nav-content').slideToggle();
                $(_barLi).toggleClass('open-item');
            });
        });
    })

    /* ============================================================
     *Oro Dropdown close prevent
     * ============================================================ */
    var dropdownToggles = $('.oro-dropdown-toggle');
    dropdownToggles.click(function(e) {
        $(this).parent().toggleClass('open')
    });

    $('html').click(function(e) {
        var $target = $(e.target);
        var clickingTarget = null;
        if ($target.hasClass('dropdown') || $target.hasClass('oro-drop')) {
            clickingTarget = $target;
        } else {
            clickingTarget = $target.closest('.dropdown, .oro-drop');
        }
        clickingTarget.addClass('_currently_clicked');
        $('.open:not(._currently_clicked)').removeClass('open')
        clickingTarget.removeClass('_currently_clicked');
    });
});

function hideProgressBar() {
    if ($('#progressbar').is(':visible')) {
        $('#progressbar').hide();
        $('#page').show();
    }
}

if (typeof Oro !== "undefined") {
    /**
     * Init page layout js and hide progress bar after hash navigation request is completed
     */
    Oro.Events.bind(
        "hash_navigation_request:complete",
        function () {
            hideProgressBar();
            initLayout();
        },
        this
    );
}

/**
 * Js updates
 */
var Oro = Oro || {};
Oro.styleForm = function(container) {
    if ($.isPlainObject($.uniform)) {
        var formElements = container.find('input:file, select:not(.select2-offscreen)');
        formElements.uniform();
        formElements.trigger('uniformInit');
    }
}

function initLayout() {
    Oro.styleForm($(document.body));

    if (typeof($.datepicker) != 'undefined') {
        $('input.datepicker').each(function (index, el) {
            el = $(el);

            el.datepicker({
                dateFormat: el.attr('data-dateformat') ? el.attr('data-dateformat') : 'm/d/y'
            });
        });
    }

    if (typeof($.timepicker) != 'undefined') {
        $('input.datetimepicker').each(function (index, el) {
            el = $(el);

            el.datetimepicker({
                dateFormat: el.attr('data-dateformat') ? el.attr('data-dateformat') : 'm/d/y',
                timeFormat: el.attr('data-timeformat') ? el.attr('data-timeformat') : 'hh:mm tt'
            });
        });
    }

    $('[data-spy="scroll"]').each(function () {
        var $spy = $(this)
        $spy.scrollspy($spy.data())
        var $spy = $(this).scrollspy('refresh');
        $('.scrollspy-nav ul.nav li').removeClass('active');
        $('.scrollspy-nav ul.nav li:first').addClass('active');
    })
    $('[data-toggle="tooltip"]').tooltip();
}

/**
 * Fix for IE8 compatibility
 */
if ( !Date.prototype.toISOString ) {

    ( function() {

        function pad(number) {
            var r = String(number);
            if ( r.length === 1 ) {
                r = '0' + r;
            }
            return r;
        }

        Date.prototype.toISOString = function() {
            return this.getUTCFullYear()
                + '-' + pad( this.getUTCMonth() + 1 )
                + '-' + pad( this.getUTCDate() )
                + 'T' + pad( this.getUTCHours() )
                + ':' + pad( this.getUTCMinutes() )
                + ':' + pad( this.getUTCSeconds() )
                + '.' + String( (this.getUTCMilliseconds()/1000).toFixed(3) ).slice( 2, 5 )
                + 'Z';
        };

    }() );
}
