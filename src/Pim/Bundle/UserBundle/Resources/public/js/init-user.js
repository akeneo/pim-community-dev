/* jshint browser:true */
/* global require */
require(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/messenger'],
function($, _, __, app, mediator, messenger) {
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
