define(
    ['jquery', 'backbone', 'pim/dialog', 'oro/navigation'],
    function ($, Backbone, Dialog, Navigation) {
        'use strict';

        return function ($form) {
            this.updated = false;
            var message     = $form.attr('data-updated-message'),
                title       = $form.attr('data-updated-title'),
                self        = this,
                formUpdated = function () {
                    self.updated = true;
                    $('#updated').show();

                    $form.off('change', formUpdated);
                    $form.find('ins.jstree-checkbox').off('click', formUpdated);

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
            $form.find('ins.jstree-checkbox').on('click', formUpdated);

            $('a[href^="/"]:not(".no-hash")').off('click').on('click', linkClicked);

            Backbone.Router.prototype.on('route', function () {
                $('a[href^="/"]:not(".no-hash")').off('click', linkClicked);
            });
        };
    }
);
