'use strict';

define(['jquery', 'backbone', 'underscore', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/layout',
    'oro/delete-confirmation', 'oro/messenger', 'bootstrap'
    ], function ($, Backbone, _, __, app, mediator, layout, DeleteConfirmation, messenger) {


    /* ============================================================
     * from layout.js
     * ============================================================ */
    return function () {
        mediator.once('tab:changed', function () {
            setTimeout(function () {
                // emulates 'document ready state' for selenium tests
                document['page-rendered'] = true;
                mediator.trigger('page-rendered');
            }, 50);
        });
        layout.init();

        /* ============================================================
         * Oro Dropdown close prevent
         * ============================================================ */
        var dropdownToggles = $('.oro-dropdown-toggle');
        dropdownToggles.click(function () {
            var $parent = $(this).parent().toggleClass('open');
            if ($parent.hasClass('open')) {
                $parent.find('input[type=text]').first().focus().select();
            }
        });

        $('html').click(function (e) {
            var $target = $(e.target);
            var clickingTarget = null;
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


        /* ============================================================
         * from height_fix.js
         * ============================================================ */

        /* dynamic height for central column */
        var debugBar = $('.sf-toolbar');
        var anchor = $('#bottom-anchor');
        var content = false;

        var initializeContent = function () {
            if (!content) {
                content = $('.scrollable-container').filter(':parents(.ui-widget)');
                content.css('overflow', 'auto');
            }
        };

        var adjustHeight = function () {
            initializeContent();

            var debugBarHeight = debugBar.length && debugBar.is(':visible') ? debugBar.height() : 0;
            var anchorTop = anchor.position().top;

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

        mediator.once('page-rendered', function () {
            if (debugBar.length) {
                waitForDebugBar();
            } else {
                adjustHeight();
            }
        });

        $(window).on('resize', adjustHeight);

        mediator.bind('route_complete', adjustReloaded);

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

        $(document).on('click', '.remove-button', function () {
            var confirm;
            var el = $(this);
            var message = el.data('message');

            confirm = new DeleteConfirmation({
                content: message
            });

            confirm.on('ok', function () {
                router.showLoadingMask();

                $.ajax({
                    url: el.data('url'),
                    type: 'DELETE',
                    success: function () {
                        el.trigger('removesuccess');
                        messenger.addMessage(
                            'success',
                            el.data('success-message'),
                            { 'hashNavEnabled': true }
                        );
                        if (el.data('redirect')) {
                            $.isActive(true);
                            Backbone.history.navigate(el.data('redirect'));
                        } else {
                            router.hideLoadingMask();
                        }
                    },
                    error: function () {
                        router.hideLoadingMask();

                        messenger.notificationMessage(
                            'error',
                            el.data('error-message') ||
                                __('Unexpected error occured. Please contact system administrator.')
                        );
                    }
                });
            });
            confirm.open();

            return false;
        });
    }
});
