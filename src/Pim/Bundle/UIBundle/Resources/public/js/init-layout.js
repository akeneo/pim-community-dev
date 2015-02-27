/*jshint browser: true*/
/*jslint browser: true, nomen: true, vars: true*/
/*global require*/

require(['oro/mediator'], function (mediator) {
    'use strict';
    mediator.once('tab:changed', function () {
        setTimeout(function () {
            // emulates 'document ready state' for selenium tests
            document['page-rendered'] = true;
            mediator.trigger('page-rendered');
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

            $(content.get().reverse()).each(function (pos, el) {
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

        mediator.once("page-rendered", function () {
            if (debugBar.length) {
                waitForDebugBar();
            } else {
                adjustHeight();
            }
        });

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
    });

    $(document).on('click', '.removeRow', function (e) {
        e.preventDefault();
        $(this).parents('*[data-content]').remove();
    });
});
