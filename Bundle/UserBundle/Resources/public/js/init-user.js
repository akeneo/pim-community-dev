/* jshint browser:true */
/* global require */
require(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/messenger',
    'oro/dialog-widget', 'jquery.dialog.extended'],
function($, _, __, app, mediator, messenger, DialogWidget) {
    'use strict';

    /* ============================================================
     * from user.js
     * ============================================================ */
    $(function() {
        function checkRoleInputs() {
            var inputs = $('#roles-list .controls').find(':checkbox');
            inputs.attr('required', inputs.filter(':checked').length > 0 ? null : 'required');
        }

        function initFlashMessages() {
            messenger.setup();
        }

        $(document).on('click', '#btn-apigen', function () {
            var el = $(this);

            $.get(el.attr('href'), function (data) {
                el.prev().text(data);
                var messageText = el.attr('data-message') + ' <strong>' + data + '</strong>';
                messenger.notificationFlashMessage('success', messageText);
            });

            return false;
        });

        $(document).on('click', '#roles-list input', function (e) {
            checkRoleInputs();
        });

        /**
         * Process role checkboxes after hash navigation request is completed
         */
        mediator.on("hash_navigation_request:complete", checkRoleInputs);

        /**
         * Process flash messages stored in queue or storage
         */
        mediator.on("hash_navigation_request:complete", initFlashMessages);

        $(document).on('change', '#btn-enable input', function(e) {
            $('.status-enabled').toggleClass('hide');
            $('.status-disabled').toggleClass('hide');
        });

        $(document).on('click', '#view-activity-btn', function (e) {
            e.stopImmediatePropagation();
            var $el = $(this),
                dialog = /** @var oro.DialogWidget */ $el.data('dialog');
            if (dialog) {
                // dialog already is opened
                return false;
            }

            $el.data('dialog', dialog = new DialogWidget({
                url: $el.attr('href'),
                dialogOptions: {
                    allowMaximize: true,
                    allowMinimize: true,
                    dblclick: 'maximize',
                    maximizedHeightDecreaseBy: 'minimize-bar',
                    width : 1000,
                    title: $el.attr('title')
                }
            }));
            dialog.once('widgetRemove', _.bind($el.removeData, $el, 'dialog'));
            dialog.render();

            return false;
        });
    });

    /* ============================================================
     * from status.js
     * ============================================================ */
    $(function () {
        var dialogBlock;

        $(".update-status a").click(function () {
            $.get($(this).attr('href'), function(data) {
                dialogBlock = $(data).dialog({
                    title: __('Update status'),
                    width: 300,
                    height: 180,
                    modal: false,
                    resizable: false
                });
            });

            return false;
        });

        $(document).on('submit', '#create-status-form', function(e) {
            $.ajax({
                type:'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(response) {
                    dialogBlock.dialog("destroy");
                }
            });

            return false;
        });
    });
});
