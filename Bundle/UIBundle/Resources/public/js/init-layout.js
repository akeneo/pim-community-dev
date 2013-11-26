/*jshint browser: true*/
/*jslint browser: true, nomen: true, vars: true*/
/*global require*/

require(['oro/mediator'], function (mediator) {
    'use strict';
    mediator.once('tab:changed', function () {
        setTimeout(function () {
            // emulates 'document ready state' for selenium tests
            document['page-rendered'] = true;
        }, 50);
    });
});

require(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/layout', 'oro/navigation',
    'oro/delete-confirmation', 'oro/messenger', 'bootstrap', 'jquery-ui', 'jquery-ui-timepicker'
    ], function ($, _, __, app, mediator, layout, Navigation, DeleteConfirmation, messenger) {
    'use strict';

    /* ============================================================
     * from layout.js
     * ============================================================ */
    $(function () {
        layout.init();

        /* hide progress bar on page ready in case we don't need hash navigation request*/
        if (!Navigation.isEnabled() || !Navigation.prototype.checkHashForUrl()) {
            if ($('#page-title').size()) {
                document.title = $('#page-title').text();
            }
            layout.hideProgressBar();
        }

        /* side bar functionality */
        $('div.side-nav').each(function () {
            var myParent = $(this),
                myParentHolder = $(myParent).parent().height() - 18;
            $(myParent).height(myParentHolder);
            /* open close bar */
            $(this).find("span.maximaze-bar").click(function () {
                if (($(myParent).hasClass("side-nav-open")) || ($(myParent).hasClass("side-nav-locked"))) {
                    $(myParent).removeClass("side-nav-locked side-nav-open");
                    if ($(myParent).hasClass('left-panel')) {
                        $(myParent).parent('div.page-container').removeClass('left-locked');
                    } else {
                        $(myParent).parent('div.page-container').removeClass('right-locked');
                    }
                    $(myParent).find('.bar-tools').css({
                        "height": "auto",
                        "overflow" : "visible"
                    });
                } else {
                    $(myParent).addClass("side-nav-open");
                    var openBarHeight = $("div.page-container").height() - 20,
                        testBarScroll = $(myParent).find('.bar-tools').height();
                    /* minus top-padding and bottom-padding */
                    $(myParent).height(openBarHeight);
                    if (openBarHeight < testBarScroll) {
                        $(myParent).find('.bar-tools').height((openBarHeight - 20)).css({
                            "overflow" : "auto"
                        });
                    }
                }
            });

            /* lock&unlock bar */
            $(this).find("span.lock-bar").click(function () {
                if ($(this).hasClass("lock-bar-locked")) {
                    $(myParent).addClass("side-nav-open")
                        .removeClass("side-nav-locked");
                    if ($(myParent).hasClass('left-panel')) {
                        $(myParent).parent('div.page-container').removeClass('left-locked');
                    } else {
                        $(myParent).parent('div.page-container').removeClass('right-locked');
                    }
                } else {
                    $(myParent).addClass("side-nav-locked")
                        .removeClass("side-nav-open");
                    if ($(myParent).hasClass('left-panel')) {
                        $(myParent).parent('div.page-container').addClass('left-locked');
                    } else {
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
                    var overlayHeight = $('#page').height(),
                        overlayWidth = $('#page > .wrapper').width();
                    $('#bar-drop-overlay').width(overlayWidth).height(overlayHeight);
                    $('#bar-drop-overlay').toggleClass('bar-open-overlay');
                });
                $(myItem).find("span.close").click(function () {
                    $(myItem).find("div.nav-box").fadeToggle("slow");
                    $('#bar-drop-overlay').toggleClass('bar-open-overlay');
                });
                $('#bar-drop-overlay').on({
                    click: function () {
                        $(myItem).find("div.nav-box").animate({
                            opacity: 0,
                            display: 'none'
                        }, function () {
                            $(this).css({
                                opacity: 1,
                                display: 'none'
                            });
                        });
                        $('#bar-drop-overlay').removeClass('bar-open-overlay');
                    }
                });
            });
            /* open content for open bar */
            $(myParent).find('ul.bar-tools > li').each(function () {
                var _barLi = $(this);
                $(_barLi).find('span.open-bar-item').click(function () {
                    $(_barLi).find('div.nav-content').slideToggle();
                    $(_barLi).toggleClass('open-item');
                });
            });
        });

        /* ============================================================
         * Oro Dropdown close prevent
         * ============================================================ */
        var dropdownToggles = $('.oro-dropdown-toggle');
        dropdownToggles.click(function (e) {
            var $parent = $(this).parent().toggleClass('open');
            if ($parent.hasClass('open')) {
                $parent.find('input[type=text]').first().focus().select();
            }
        });
        $('body').on('focus.dropdown.data-api', '[data-toggle=dropdown]', _.debounce(function (e) {
            $(e.target).parent().find('input[type=text]').first().focus();
        }, 10));

        $('html').click(function (e) {
            var $target = $(e.target),
                clickingTarget = null;
            if ($target.hasClass('dropdown') || $target.hasClass('oro-drop')) {
                clickingTarget = $target;
            } else {
                clickingTarget = $target.closest('.dropdown, .oro-drop');
            }
            clickingTarget.addClass('_currently_clicked');
            $('.open:not(._currently_clicked)').removeClass('open');
            clickingTarget.removeClass('_currently_clicked');
        });

        $('#main-menu').mouseover(function () {
            $('.open').removeClass('open');
        });
    });

    /**
     * Init page layout js and hide progress bar after hash navigation request is completed
     */
    mediator.bind("hash_navigation_request:complete", function () {
        layout.hideProgressBar();
        layout.init();
    });

    /* ============================================================
     * from height_fix.js
     * ============================================================ */
    (function () {
        /* dynamic height for central column */
        var debugBar = $('.sf-toolbar'),
            anchor = $('#bottom-anchor'),
            content = false;

        var initializeContent = function () {
            if (!content) {
                content = $('.scrollable-container').filter(':parents(.ui-widget)');
                content.css('overflow', 'auto');

                $('.scrollable-substructure').css({
                    'padding-bottom': '0px',
                    'margin-bottom': '0px'
                });
            }
        };

        var adjustHeight = function () {
            initializeContent();

            var debugBarHeight = debugBar.length && debugBar.is(':visible') ? debugBar.height() : 0,
                anchorTop = anchor.position().top;

            content.each(function (pos, el) {
                el = $(el);
                el.height(anchorTop - el.position().top - debugBarHeight);
            });

            layout.adjustScrollspy();
        };

        var tries = 0;
        var waitForDebugBar = function () {
            if (debugBar.children().length) {
                window.setTimeout(adjustHeight, 500);
            } else if (tries < 100) {
                tries += 1;
                window.setTimeout(waitForDebugBar, 500);
            }
        };

        var adjustReloaded = function () {
            content = false;
            adjustHeight();
        };

        if (!anchor.length) {
            anchor = $('<div id="bottom-anchor"/>')
                .css({
                    position: 'fixed',
                    bottom: '0',
                    left: '0',
                    width: '1px',
                    height: '1px'
                })
                .appendTo($(document.body));
        }

        if (debugBar.length) {
            waitForDebugBar();
        } else {
            adjustHeight();
        }

        $(window).on('resize', adjustHeight);

        mediator.bind("hash_navigation_request:complete", adjustReloaded);
    }());

    /* ============================================================
     * from form_buttons.js
     * ============================================================ */
    $(document).on('click', '.action-button', function () {
        var actionInput = $('input[name = "input_action"]');
        actionInput.val($(this).attr('data-action'));
        $('#' + actionInput.attr('data-form-id')).submit();
    });

    /* ============================================================
     * from remove.confirm.js
     * ============================================================ */
    $(function () {
        $(document).on('click', '.remove-button', function (e) {
            var confirm,
                el = $(this),
                message = el.data('message');

            confirm = new DeleteConfirmation({
                content: message
            });

            confirm.on('ok', function () {
                var navigation = Navigation.getInstance();
                if (navigation) {
                    navigation.loadingMask.show();
                }

                $.ajax({
                    url: el.data('url'),
                    type: 'DELETE',
                    success: function (data) {
                        el.trigger('removesuccess');
                        messenger.addMessage('success', el.data('success-message'), {'hashNavEnabled': Navigation.isEnabled()});
                        if (el.data('redirect')) {
                            $.isActive(true);
                            if (navigation) {
                                navigation.setLocation(el.data('redirect'));
                            } else {
                                window.location.href = el.data('redirect');
                            }
                        } else if (navigation) {
                            navigation.loadingMask.hide();
                        }
                    },
                    error: function () {
                        if (navigation) {
                            navigation.loadingMask.hide();
                        }

                        messenger.notificationMessage(
                            'error',
                            el.data('error-message') ||  __('Unexpected error occured. Please contact system administrator.')
                        );
                    }
                });
            });
            confirm.open();

            return false;
        });
    });

    /* ============================================================
     * from form/collection.js'
     * ============================================================ */
    $(document).on('click', '.add-list-item', function (e) {
        e.preventDefault();
        var cList  = $(this).siblings('.collection-fields-list'),
            widget = cList.attr('data-prototype').replace(/__name__/g, cList.children().length),
            data = $('<div/>').html(widget);

        data.children().appendTo(cList);
        /* temporary solution need add init only for new created row */
        layout.styleForm(data);
        /* temporary solution finish */
    });

    $(document).on('click', '.removeRow', function (e) {
        e.preventDefault();
        $(this).parents('*[data-content]').remove();
    });
});
