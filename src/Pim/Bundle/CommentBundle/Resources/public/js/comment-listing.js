require(
    ['jquery', 'oro/messenger', 'pim/dialog'],
    function ($, messenger, Dialog) {
        'use strict';
        $('.tab-comment').on('click', '.comment-delete-dialog', function () {
            var $el      = $(this),
                $comment = $($el.data('comment')),
                message  = $el.data('message'),
                title    = $el.data('title'),
                doAction = function () {
                    $.ajax({
                        url: $el.attr('data-url'),
                        type: 'POST',
                        headers: { accept:'application/json' },
                        data: { _method: $el.data('method') },
                        success: function() {
                            $comment.remove();
                            messenger.notificationFlashMessage('success', $el.attr('data-success-message'));
                        },
                        error: function(xhr) {
                            messenger.notificationFlashMessage(
                                'error',
                                (xhr.responseJSON && xhr.responseJSON.message) ?
                                    xhr.responseJSON.message :
                                    $el.attr('data-error-message')
                            );
                        }
                    });
                };
            $el.off('click');
            Dialog.confirm(message, title, doAction);

            return false;
        });

        $(".tab-comment .comment-create").on("click", function() {
            $(".tab-comment .active").removeClass("active");
            $(this).addClass("active");
        });

        $(".tab-comment .cancel").on("click", function(e) {
            e.stopPropagation();
            $(".tab-comment .active").removeClass("active");
            $(".tab-comment textarea").val('');
        });
    }
);
