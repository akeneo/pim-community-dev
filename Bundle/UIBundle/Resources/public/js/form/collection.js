$(function () {
    $(document).on('click', '.add-list-item', function (event) {
        event.preventDefault();
        var cList  = $(this).siblings('.collection-fields-list');
        var cCount = cList.children().length;
        var widget = cList.attr('data-prototype').replace(/__name__/g, cCount++);

        var data = $('<div/>').html(widget);
        data.children().appendTo(cList);
        /* temporary solution need add init only for new created row */
        Oro.styleForm(data);
        /* temporary solution finish */
    });

    $(document).on('click', '.removeRow', function (event) {
        event.preventDefault();
        $(this).parents('*[data-content]').remove();
    });
});
