$(function() {
    $(document).on('click', '.entity-extend-apply', function (e) {
        new Oro.widget.DialogView({
            url: $(this).attr('href'),
            dialogOptions: {
                allowMaximize: false,
                allowMinimize: false,
                //dblclick: 'maximize',
                maximizedHeightDecreaseBy: 'minimize-bar',
                width : 1000,
                minHeight: 560,
                resizable: false,
                title: $(this).attr('title')
            }
        }).render();

        return false;
    });
});
