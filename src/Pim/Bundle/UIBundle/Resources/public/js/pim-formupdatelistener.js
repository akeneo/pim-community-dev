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
                formUpdated = function (e) {
                    var $target = $(e.target);
                    if ($target.parents('div.filter-box').length || $target.parents('ul.icons-holder').length || $target.hasClass('exclude')) {
                        return;
                    }
                    self.updated = true;
                    $('#entity-updated').css('opacity', 1);

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
            $form.on('refresh', function() {
                self.updated = false;
                $('#entity-updated').css('opacity', 0);
            });

            $('a[href^="/"]:not(".no-hash")').off('click').on('click', linkClicked);
            $form.on('click', 'a[href^="/"]:not(".no-hash")', linkClicked);

            Backbone.Router.prototype.on('route', function () {
                $('a[href^="/"]:not(".no-hash")').off('click', linkClicked);
                $(window).off('beforeunload');
            });
        };
    }
);
