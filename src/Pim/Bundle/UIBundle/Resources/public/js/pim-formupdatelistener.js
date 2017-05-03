/* global console */
define(
    ['jquery', 'backbone', 'pim/dialog', 'pim/router'],
    function ($, Backbone, Dialog, router) {
        'use strict';

        return function ($form) {
            this.updated = false;
            var message = $form.attr('data-updated-message');
            if (!message) {
                console.warn('FormUpdateListener: message not provided.');

                return;
            }
            var title = $form.attr('data-updated-title');
            var self  = this;

            var formUpdated = function (e) {
                var $target = $(e.target);
                if ($target.parents('div.filter-box').length ||
                    $target.parents('ul.icons-holder').length ||
                    $target.hasClass('exclude')) {

                    return;
                }
                self.updated = true;
                $('#entity-updated').show().css('opacity', 1);

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
            };

            var linkClicked = function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                var url      = $(this).attr('href');
                var doAction = function () {
                    router.redirect(url);
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
            $form.on('refresh', function () {
                self.updated = false;
                $('#entity-updated').css('opacity', 0).hide();
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
