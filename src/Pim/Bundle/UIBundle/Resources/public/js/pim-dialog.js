define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'pim/router', 'bootstrap-modal'],
    function ($, _, Backbone, __, router) {
        'use strict';

        /**
         * Dialog class purposes an easier way to call ModalDialog components
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @uses Backbone.BootstrapModal
         *
         * Example:
         *      Dialog.alert('{{ 'MyMessage'|trans }}', 'MyTitle');
         */
        return {
            /**
             * Open a modal dialog without cancel button
             * @param string content
             * @param string title
             */
            alert: function (content, title) {
                if (!_.isUndefined(Backbone.BootstrapModal)) {
                    var alert = new Backbone.BootstrapModal({
                        allowCancel: false,
                        title: title,
                        content: content,
                        okText: __('OK')
                    });
                    alert.open();
                } else {
                    window.alert(content);
                }
            },

            /**
             * Open a modal dialog with cancel button and specific redirection when
             * @param string content
             * @param string title
             * @param string okText
             * @param string location
             */
            redirect: function (content, title, okText, location) {
                if (!_.isUndefined(Backbone.BootstrapModal)) {
                    var redirectModal = new Backbone.BootstrapModal({
                        allowCancel: true,
                        title: title,
                        content: content,
                        okText: okText,
                        cancelText: __('Cancel')
                    });

                    redirectModal.on('ok', function () {
                        router.redirect(location);
                    });

                    $('.modal-body a', redirectModal.el).on('click', function () {
                        redirectModal.close();
                    });

                    redirectModal.open();
                } else {
                    window.alert(content);
                }
            },

            /**
             * Open a confirm modal dialog to validate the action made by user
             * If user validate its action, a js callback function is called
             * @param string content
             * @param string title
             * @param function callback
             */
            confirm: function (content, title, callback) {
                var deferred = $.Deferred();

                var success = function () {
                    deferred.resolve();
                    (callback || $.noop)();
                };
                var cancel = function () {
                    deferred.reject();
                };
                if (!_.isUndefined(Backbone.BootstrapModal)) {
                    var confirm = new Backbone.BootstrapModal({
                        title: title,
                        content: content,
                        okText: __('OK'),
                        cancelText: __('Cancel')
                    });
                    confirm.on('ok', success);
                    confirm.on('cancel', cancel);
                    confirm.open();
                } else {
                    (window.confirm(content) ? success : cancel)();
                }

                return deferred.promise();
            }
        };
    }
);
