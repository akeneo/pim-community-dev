define(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'oro/messenger', 'pim/dialog'],
    function ($, __, mediator, Navigation, messenger, Dialog) {
        'use strict';
        $(function(){
            if ($.isPlainObject($.uniform)) {
                $.uniform.restore();
            }

            // DELETE request for delete buttons
            $(document).on('click', '[data-comment-dialog]', function () {
                var $el      = $(this),
                    message  = $el.data('message'),
                    title    = $el.data('title'),
                    doAction = function () {
                        $.ajax({
                            url: $el.attr('data-url'),
                            type: 'POST',
                            headers: { accept:'application/json' },
                            data: { _method: $el.data('method') },
                            success: function() {
                                alert('YEAH');
                                //navigation.addFlashMessage('success', $el.attr('data-success-message'));
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
                if ($el.data('dialog') === 'confirm') {
                    Dialog.confirm(message, title, doAction);
                } else {
                    Dialog.alert(message, title);
                }

                return false;
            });
        });
    }
);
