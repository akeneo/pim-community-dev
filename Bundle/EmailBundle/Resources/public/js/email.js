$(function () {
    var assignShowEmailBodyDialog = function (btn) {
        btn.addClass('no-hash');
        btn.on('click', function () {
            new Oro.widget.DialogView({
                url: $(this).attr('href'),
                dialogOptions: {
                    allowMaximize: true,
                    allowMinimize: true,
                    dblclick: 'maximize',
                    maximizedHeightDecreaseBy: 'minimize-bar',
                    width: 1000,
                    title: $(this).attr('title')
                }
            }).render();
            return false;
        });
    };

    assignShowEmailBodyDialog($('.view-email-body-btn'));

    if (!_.isUndefined(Oro.Events)) {
        Oro.Events.on('top_search_request:complete', function () {
            assignShowEmailBodyDialog($('#search-dropdown .view-email-search-suggestion-btn'));
            return false;
        });
    }
});
