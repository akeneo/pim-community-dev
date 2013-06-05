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
            if (!_.isUndefined(Oro.Messages)) {
                Oro.Messages.showMessage('success', messageText);
            } else {
                alert(messageText);
            }
        })

        return false;
    });

    $(document).on('click', '#roles-list input', function (e) {
        checkRoleInputs();
    });

    $(document).on('click', '#btn-remove-user', function (e) {
        var el = $(this),
            message = el.attr('data-message'),
            doAction = function() {
                $.ajax({
                    url: Routing.generate('oro_api_delete_user', { id: el.attr('data-id') }),
                    type: 'DELETE',
                    success: function (data) {
                        if (Oro.hashNavigationEnabled()) {
                            Oro.Navigation.prototype.setLocation(Routing.generate('oro_user_index'))
                        } else {
                            window.location.href = Routing.generate('oro_user_index');
                        }
                    }
                });
            };

        if (!_.isUndefined(Oro.BootstrapModal)) {
            var confirm = new Oro.BootstrapModal({
                title: 'Delete Confirmation',
                content: message,
                okText: 'Yes, Delete',
                okButtonClass: 'btn-large  btn-danger'

            });
            confirm.on('ok', doAction);
            confirm.open();
        } else if (window.confirm(message)) {
            doAction();
        }

        return false;
    })
    $(document).on('click', '#btn-remove-role', function (e) {
        var el = $(this),
            message = el.attr('data-message'),
            doAction = function() {
                $.ajax({
                    url: Routing.generate('oro_api_delete_role', { id: el.attr('data-id') }),
                    type: 'DELETE',
                    success: function (data) {
                        if (Oro.hashNavigationEnabled()) {
                            Oro.Navigation.prototype.setLocation(Routing.generate('oro_user_role_index'))
                        } else {
                            window.location.href = Routing.generate('oro_user_role_index');
                        }
                    }
                });
            };

        if (!_.isUndefined(Oro.BootstrapModal)) {
            var confirm = new Oro.BootstrapModal({
                title: 'Delete Confirmation',
                content: message,
                okText: 'Yes, Delete',
                okButtonClass: 'btn-large  btn-danger'

            });
            confirm.on('ok', doAction);
            confirm.open();
        } else if (window.confirm(message)) {
            doAction();
        }

        return false;
    });
    $(document).on('click', '#btn-remove-group', function (e) {
        var el = $(this),
            message = el.attr('data-message'),
            doAction = function() {
                $.ajax({
                    url: Routing.generate('oro_api_delete_group', { id: el.attr('data-id') }),
                    type: 'DELETE',
                    success: function (data) {
                        if (Oro.hashNavigationEnabled()) {
                            Oro.Navigation.prototype.setLocation(Routing.generate('oro_user_group_index'))
                        } else {
                            window.location.href = Routing.generate('oro_user_group_index');
                        }
                    }
                });
            };

        if (!_.isUndefined(Oro.BootstrapModal)) {
            var confirm = new Oro.BootstrapModal({
                title: 'Delete Confirmation',
                content: message,
                okText: 'Yes, Delete',
                okButtonClass: 'btn-large btn-danger'

            });
            confirm.on('ok', doAction);
            confirm.open();
        } else if (window.confirm(message)) {
            doAction();
        }

        return false;
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
        if ($(':ui-dialog').has('#historyDatagridGridTemplate').length) {
            return e.stopPropagation();
        }

        var scrollable = $('.scrollable-container');
        var container = scrollable.length ? scrollable.get(scrollable.length - 1) : '#container';
        new Oro.widget.DialogView({
            url: $(this).attr('href'),
            dialogOptions: {
                appendTo: container,
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
