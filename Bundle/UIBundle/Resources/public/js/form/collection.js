$(function () {
    $(document).on('click', '.add-list-item', function (event) {
        event.preventDefault();
        var cList  = $(this).siblings('.collection-fields-list'),
            cCount = cList.children().length;
            widget = cList.attr('data-prototype').replace(/__name__/g, cCount++);

        $('<div></div>').html(widget).appendTo(cList);
        /* temporary solution need add init onlu for new createed row */
        if ($.isPlainObject($.uniform)) {
            $('input:file, select').uniform();
        }
        /* temporary solution finish */
    });

    $(document).on('click', '.removeRow', function (event) {
        event.preventDefault();
        $(this).parents('*[data-content]').remove();
    });
});
