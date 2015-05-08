require(['underscore', 'jquery', 'oro/messenger', 'routing'], function(_, $, messenger, Routing) {
    $(function() {
        $('#pim_comment_comment_create').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: Routing.generate('pim_comment_comment_create'),
                type: 'POST',
                data: $('#pim_comment_comment_create').serialize(),
                success: function(data) {
                    $('#comment_threads > li:first-child').after('<li>' + data + '</li>');
                    $('#pim_comment_comment_body_create').val('');
                    if (0 < $('li.no-data').length) {
                        $('li.no-data').remove();
                    }
                    messenger.notificationFlashMessage('success', _.__('pim_comment.comment.flash.create.success'));
                },
                error: function(xhr) {
                    messenger.notificationFlashMessage(
                        'error',
                        (xhr.responseJSON && xhr.responseJSON.message) ?
                            xhr.responseJSON.message :
                            _.__('pim_comment.comment.flash.create.error')
                    );
                }
            });
        });
    });
});
