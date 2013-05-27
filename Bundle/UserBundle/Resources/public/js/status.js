$(function () {
    var dialogBlock;

    $(".update-status a").click(function () {
        $.get($(this).attr('href'), function(data) {
            dialogBlock = $(data).dialog({
                title: "Update status",
                width: 300,
                height: 180,
                modal: false,
                resizable: false
            });
        })

        return false;
    });

    $(document).on('submit', '#create-status-form', function(e) {
        $.ajax({
            type:'POST',
            url: $($(this)).attr('action'),
            data:$($(this)).serialize(),
            success: function(response) {
                dialogBlock.dialog("destroy");
            }
        });

         return false;
    });
});
