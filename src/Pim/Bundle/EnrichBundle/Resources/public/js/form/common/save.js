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
        'jquery',
        'oro/translator',
        'pim/form',
        'oro/mediator',
        'oro/loading-mask',
        'oro/messenger'
    ],
    function (
        $,
        __,
        BaseForm,
        mediator,
        LoadingMask,
        messenger
    ) {
        return BaseForm.extend({
            loadingMask: null,
            updateFailureMessage: __('pim_enrich.entity.info.update_failed'),
            updateSuccessMessage: __('pim_enrich.entity.info.update_successful'),
            label: __('pim_enrich.entity.save.label'),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

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
             */
            postSave: function () {
                this.getRoot().trigger('pim_enrich:form:entity:post_save');

                messenger.notify(
                    'success',
                    this.updateSuccessMessage
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
                        mediator.trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON}
                        );
                        break;
                    case 500:
                        /* global console */
                        var message = response.responseJSON ? response.responseJSON : response;

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
