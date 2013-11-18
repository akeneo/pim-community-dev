/* jshint browser:true */
/* global require */
require(['jquery', 'underscore', 'oro/translator', 'oro/modal'],
function($, _, __, Modal) {
    'use strict';
    $(function() {
        $(document).on('click', '.entity-extend-apply', function (e) {
            var el = $(this),
                message = el.data('message'),
                doAction = function() {
                    confirmUpdate.preventClose(function(){});

                    var url = $(el).attr('href').substr(21),
                        progressbar = $('#progressbar').clone();
                    progressbar
                        .attr('id', 'confirmUpdateLoading')
                        .css({'display':'block', 'margin': '0 auto'})
                        .find('h3').remove();

                    confirmUpdate.$content.parent().find('a.cancel').hide();
                    confirmUpdate.$content.parent().find('a.close').hide();
                    confirmUpdate.$content.parent().find('a.btn-primary').replaceWith(progressbar);

                    $('#confirmUpdateLoading').show();
                    window.location.href = url;
                },
                /** @type oro.Modal */
                confirmUpdate = new Modal({
                    allowCancel: true,
                    cancelText: __('Cancel'),
                    title: __('Schema update confirmation'),
                    content: '<p>' + __('Your config changes will be applied to schema.') +
                        '</p></p>' + __('It may take few minutes...') + '</p>',
                    okText: __('Yes, Proceed'),
                    okButtonClass: 'btn-primary'
                });
            confirmUpdate.on('ok', doAction);
            confirmUpdate.open();

            return false;
        });
    });
});
