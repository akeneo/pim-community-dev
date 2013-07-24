$(function() {
    $(document).on('click', '.entity-extend-apply', function (e) {
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
