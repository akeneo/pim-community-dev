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

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator',
        'pim/router',
        'pim/template/grid/mass-actions-confirm',
        'bootstrap-modal'
    ],
    function ($, _, Backbone, __, router, template) {
        'use strict';

        const Dialog = {
            template: _.template(template),

            /**
             * Open a modal dialog without cancel button
             * @param string content
             * @param string title
             */
            alert: function (content, title, subTitle) {
                const alert = new Backbone.BootstrapModal({
                    type: __(subTitle) || '',
                    allowCancel: false,
                    title: title,
                    content: content,
                    okText: __('OK'),
                    cancelText: __('Cancel'),
                    template: this.template,
                    buttonClass: 'AknButton--action'
                });

                alert.$el.addClass('modal--fullPage');

                alert.open();
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
            confirm: function (content, title, callback, subTitle, buttonClass, buttonText) {
                const deferred = $.Deferred();

                const confirm = new Backbone.BootstrapModal({
                    type: __(subTitle || ''),
                    title: __(title),
                    content: __(content),
                    okText: buttonText || __('OK'),
                    cancelText: __('Cancel'),
                    buttonClass: buttonClass || 'AknButton--action',
                    template: this.template,
                    allowCancel: true
                });

                confirm.$el.addClass('modal--fullPage');

                confirm.on('ok', function () {
                    deferred.resolve();
                    (callback || $.noop)();
                });

                confirm.on('cancel', function () {
                    this.close();
                    this.remove();
                    deferred.reject();
                });

                confirm.open();

                return deferred.promise();
            },

            confirmDelete: function(content, title, callback, subTitle, buttonText) {
                return Dialog.confirm.apply(this, [
                    content,
                    title,
                    callback,
                    subTitle,
                    'AknButton--important',
                    __(buttonText) || __('Delete')
                ]);
            }
        };

        return Dialog;
    }
);
