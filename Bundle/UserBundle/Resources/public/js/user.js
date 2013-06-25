$(function() {
    function checkRoleInputs() {
        inputs = $('#roles-list .controls').find(':checkbox');
        inputs.attr('required', inputs.filter(':checked').length > 0 ? null : 'required');
    }

    $(document).on('click', '#btn-apigen', function (e) {
        el = $(this);

        $.get(el.attr('href'), function (data) {
            el.prev().text(data);
            var messageText = el.attr('data-message') + ' <strong>' + data + '</strong>';
            if (!_.isUndefined(Oro.NotificationFlashMessage)) {
                Oro.NotificationFlashMessage('success', messageText);
            } else {
                alert(messageText);
            }
        })

        return false;
    });

    $(document).on('click', '#roles-list input', function (e) {
        checkRoleInputs();
    });

    /**
     * Process role checkboxes after hash navigation request is completed
     */
    Oro.Events.bind(
        "hash_navigation_request:complete",
        function () {
            checkRoleInputs();
        },
        this
    );

    $(document).on('change', '#btn-enable input', function(e) {
        $('.status-enabled').toggleClass('hide');
        $('.status-disabled').toggleClass('hide');
    });

    $(document).on('click', '#view-activity-btn', function (e) {
        new Oro.widget.DialogView({
            url: $(this).attr('href'),
            dialogOptions: {
                allowMaximize: true,
                allowMinimize: true,
                dblclick: 'maximize',
                maximizedHeightDecreaseBy: 'minimize-bar',
                width : 1000,
                title: $(this).attr('title')
            }
        }).render();

        return false;
    });
});
