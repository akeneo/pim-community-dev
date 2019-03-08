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
        'bootstrap-modal'
    ],
    function ($, _, Backbone, __, router) {
        'use strict';

        const Dialog = {
            /**
             * Returns class name for modal illustration
             */
            getIllustrationClass: function(entityType = '') {
                return entityType.toLowerCase().split(' ').join('-');
            },

            /**
             * Open a modal dialog without cancel button
             * @param {String} content The message in the modal
             * @param {String} title The title of the modal
             * @param {String} subTitle The subtitle for the modal
             */
            alert: function (content, title, subTitle) {
                const alert = new Backbone.BootstrapModal({
                    type: __(subTitle) || '',
                    allowCancel: false,
                    title: title,
                    content: content,
                    okText: __('pim_common.ok'),
                    buttonClass: 'AknButton--action',
                    illustrationClass: this.getIllustrationClass(subTitle)
                });

                alert.$el.addClass('modal--fullPage');

                alert.open();
            },

            /**
             * Open a modal dialog with cancel button and specific redirection when
             * @param {String} content
             * @param {String} title
             * @param {String} okText
             * @param {String} location
             */
            redirect: function (content, title, okText, location) {
                if (!_.isUndefined(Backbone.BootstrapModal)) {
                    var redirectModal = new Backbone.BootstrapModal({
                        title: title,
                        content: content,
                        okText: okText,
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
             * @param  {String}   content           Message inside the modal
             * @param  {String}   title             Title of the modal
             * @param  {Function} callback          Action to execute after validation
             * @param  {String}   subTitle          The subtitle (can be entity type)
             * @param  {String}   buttonClass       The class for OK button
             * @param  {String}   buttonText        The OK button label
             * @param  {String}   illustrationClass Class for the illustration
             * @return {Promise}
             */
            confirm: function (content, title, callback, subTitle, buttonClass, buttonText, illustrationClass) {
                const deferred = $.Deferred();

                const confirm = new Backbone.BootstrapModal({
                    type: __(subTitle || ''),
                    title: __(title),
                    innerDescription: __(content),
                    content: '',
                    okText: __(buttonText) || __('pim_common.ok'),
                    cancelText: __('pim_common.cancel'),
                    buttonClass: buttonClass || 'AknButton--action',
                    allowCancel: true,
                    illustrationClass: illustrationClass || this.getIllustrationClass(subTitle)
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

            /**
             * Open a Confirm deletion modal and execute callback after
             * user validation
             * @param  {String}   content    The message text
             * @param  {String}   title      The title
             * @param  {Function} callback   Action to execute after validation
             * @param  {String}   subTitle   The entity type
             * @param  {String}   buttonText The 'ok' button label
             */
            confirmDelete: function(content, title, callback, subTitle, buttonText) {
                return Dialog.confirm.apply(this, [
                    content,
                    title,
                    callback,
                    subTitle,
                    'AknButton--important',
                    __(buttonText) || __('Delete'),
                    'delete'
                ]);
            }
        };

        return Dialog;
    }
);
