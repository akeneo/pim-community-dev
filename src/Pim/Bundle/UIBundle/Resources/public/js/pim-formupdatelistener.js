define(
    ['jquery', 'backbone', 'pim/dialog', 'oro/navigation'],
    function ($, Backbone, Dialog, Navigation) {
        'use strict';

        return function ($form) {
            this.updated = false;
            var message = $form.attr('data-updated-message');
            if (!message) {
                console.error('FormUpdateListener: message not provided.');
                return;
            }
            var title = $form.attr('data-updated-title'),
                self  = this,
                formUpdated = function () {
                    self.updated = true;
                    $('#updated').show();

                    $form.off('change', formUpdated);
                    $(document).off('click', '#' + $form.attr('id') + ' ins.jstree-checkbox', formUpdated);

                    $form.find('button[type="submit"]').on('click', function () {
                        self.updated = false;
                    });

                    $(window).on('beforeunload', function () {
                        if (self.updated) {
                            return message;
                        }
                    });
                },
                linkClicked = function (e) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    var url      = $(this).attr('href'),
                        doAction = function () {
                            Navigation.getInstance().setLocation(url);
                        };
                    if (!self.updated) {
                        doAction();
                    } else {
                        Dialog.confirm(message, title, doAction);
                    }
                    return false;
                };

            $form.on('change', formUpdated);
            $(document).on('click', '#' + $form.attr('id') + ' ins.jstree-checkbox', formUpdated);

            $('a[href^="/"]:not(".no-hash")').off('click').on('click', linkClicked);

            Backbone.Router.prototype.on('route', function () {
                $('a[href^="/"]:not(".no-hash")').off('click', linkClicked);
                $(window).off('beforeunload');
            });
        };
    }
);
