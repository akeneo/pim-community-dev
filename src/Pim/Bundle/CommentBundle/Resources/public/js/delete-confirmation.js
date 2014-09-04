define(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'oro/messenger', 'pim/dialog'],
    function ($, __, mediator, Navigation, messenger, Dialog) {
        'use strict';
        // DELETE request for delete buttons
        $(document).on('click', '.comment-dialog', function () {
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
                            var navigation = Navigation.getInstance();
                            $comment.remove();
                            navigation.addFlashMessage('success', $el.attr('data-success-message'));
                        },
                        error: function(xhr) {
                            messenger.notificationFlashMessage(
                                'error',
                                (xhr.responseJSON && xhr.responseJSON.message) ?
                                    xhr.responseJSON.message :
                                    $el.attr('data-error-message'));
                        }
                    });
                };
            $el.off('click');
            Dialog.confirm(message, title, doAction);

            return false;
        });
    }
);
