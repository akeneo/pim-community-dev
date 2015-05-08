define(
    ['underscore', 'jquery', 'oro/messenger', 'routing'],
    function(_, $, messenger, Routing) {
        return function(commentId) {

            $(function() {
                $('#pim_comment_comment_reply_' + commentId).on('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: Routing.generate('pim_comment_comment_reply'),
                        type: 'POST',
                        data: $('#pim_comment_comment_reply_' + commentId).serialize(),
                        success: function(data) {
                            $('#comment' + commentId).replaceWith(data);
                            $('#pim_comment_comment_body_reply_' + commentId).val('');

                            messenger.notificationFlashMessage('success', _.__('pim_comment.comment.flash.reply.success'));
                        },
                        error: function(xhr) {
                            messenger.notificationFlashMessage(
                                    'error',
                                    (xhr.responseJSON && xhr.responseJSON.message) ?
                                            xhr.responseJSON.message :
                                            _.__('pim_comment.comment.flash.reply.error')
                            );
                        }
                    });
                });

                $(".tab-comment .comment-thread").on("click", function() {
                    $(".tab-comment .active").removeClass("active");
                    $(this).addClass("active");
                });

                $(".tab-comment .cancel").on("click", function(e) {
                    e.stopPropagation();
                    $(".tab-comment .active").removeClass("active");
                    $(".tab-comment textarea").val('');
                });
            });
        };
    });
