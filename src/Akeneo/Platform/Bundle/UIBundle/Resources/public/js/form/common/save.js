'use strict';

/**
 * Save extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'oro/translator',
        'pim/form',
        'oro/mediator',
        'oro/loading-mask',
        'oro/messenger'
    ],
    function (
        __,
        BaseForm,
        mediator,
        LoadingMask,
        messenger
    ) {
        return BaseForm.extend({
            loadingMask: null,
            updateFailureMessage: __('pim_enrich.entity.fallback.flash.update.fail'),
            updateSuccessMessage: __('pim_enrich.entity.fallback.flash.update.success'),
            isFlash: true,
            label: __('pim_common.save'),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                if (this.config.hasOwnProperty('updateSuccessMessage')) {
                    this.updateSuccessMessage = __(this.config.updateSuccessMessage);
                }
                if (this.config.hasOwnProperty('updateFailureMessage')) {
                    this.updateFailureMessage = __(this.config.updateFailureMessage);
                }

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('save-buttons:register-button', {
                    className: 'save',
                    priority: 200,
                    label: this.label,
                    events: {
                        'click .save': this.save.bind(this)
                    }
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Save the current form
             */
            save: function () {
                throw new Error('This method must be implemented');
            },

            /**
             * Show the loading mask
             */
            showLoadingMask: function () {
                this.loadingMask = new LoadingMask();
                this.loadingMask.render().$el.appendTo(this.getRoot().$el).show();
            },

            /**
             * Hide the loading mask
             */
            hideLoadingMask: function () {
                this.loadingMask.hide().$el.remove();
            },

            /**
             * What to do after a save
             *
             * @param {any} data
             */
            postSave: function (data) {
                this.getRoot().trigger('pim_enrich:form:entity:post_save', data);

                messenger.notify(
                    'success',
                    this.updateSuccessMessage,
                    {flash: this.isFlash}
                );
            },

            /**
             * On save fail
             *
             * @param {Object} response
             */
            fail: function (response) {
                switch (response.status) {
                    case 400:
                        this.getRoot().trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON}
                        );
                        break;
                    case 500:
                        /* global console */
                        const message = response.responseJSON ? response.responseJSON : response;

                        console.error('Errors:', message);
                        this.getRoot().trigger('pim_enrich:form:entity:error:save', message);
                        break;
                    default:
                }

                messenger.notify(
                    'error',
                    this.updateFailureMessage
                );
            }
        });
    }
);
