$(function() {
    $(document).on('click', '.entity-extend-apply', function (e) {
        var el = $(this);
        var message = el.data('message');
        var doAction = function() {

            confirmUpdate.preventClose(function(){});

            var url = el.attr('href');
            var progressbar = $('#progressbar').clone();
            progressbar
                .attr('id', 'confirmUpdateLoading')
                .css({'display':'block', 'margin': '0 auto'})
            progressbar.find('h3').remove();

            confirmUpdate.$content.parent().find('a.btn-danger').replaceWith(progressbar);
            confirmUpdate.$content.parent().find('a.close').hide();
            $('#confirmUpdateLoading').show();

            window.location.href = url;
        };

        if (!_.isUndefined(Oro.BootstrapModal)) {
            var confirmUpdate = new Oro.BootstrapModal({
                //allowCancel: false,
                title: 'Schema update confirmation',
                content: '<p>Your config changes will be applied to schema.</p></p>It may take approximately 1..2 minutes.</p>',
                okText: 'Yes, Proceed'
            });
            confirmUpdate.on('ok', doAction);
            confirmUpdate.open();
        } else if (window.confirm(message)) {
            doAction();
        }

        return false;
    });
});
