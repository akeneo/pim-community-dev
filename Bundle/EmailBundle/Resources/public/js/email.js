$(function () {
    var showEmailEntityDialog = function (el) {
        new Oro.widget.DialogView({
            url: $(el).attr('href'),
            dialogOptions: {
                allowMaximize: true,
                allowMinimize: true,
                dblclick: 'maximize',
                maximizedHeightDecreaseBy: 'minimize-bar',
                width: 1000,
                title: $(el).attr('title')
            }
        }).render();
    };

    $(document).on('click', '.view-email-entity-btn', function (e) {
        showEmailEntityDialog(this);
        e.preventDefault();
    });
    if (!_.isUndefined(Oro.Events)) {
        Oro.Events.on('top_search_request:complete', function () {
            $('#search-dropdown').find('.view-email-search-suggestion-btn').on('click', function (e) {
                showEmailEntityDialog(this);
                e.preventDefault();
            });
            e.preventDefault();
        });
    }
});
