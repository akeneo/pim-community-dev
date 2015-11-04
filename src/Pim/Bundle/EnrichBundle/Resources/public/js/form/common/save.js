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
        'underscore',
        'module',
        'pim/form',
        'oro/mediator',
        'oro/loading-mask',
        'oro/messenger',
        'text!pim/template/form/common/save'
    ],
    function (
        $,
        _,
        module,
        BaseForm,
        mediator,
        LoadingMask,
        messenger,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            updateFailureMessage: _.__('pim_enrich.entity.info.update_failed'),
            updateSuccessMessage: _.__('pim_enrich.entity.info.update_successful'),
            events: {
                'click': 'save'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    label: _.__('pim_enrich.entity.save.label')
                }));
            },

            /**
             * Save the current form
             */
            save: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                this.getRoot().trigger('pim_enrich:form:entity:pre_save');
                $.ajax({
                    method: 'POST',
                    url: this.getSaveUrl(),
                    contentType: 'application/json',
                    data: JSON.stringify(this.getFormData())
                })
                .then(this.postSave.bind(this))
                .fail(this.fail.bind(this))
                .always(function () {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Get the save url
             *
             * @return {String}
             */
            getSaveUrl: function () {
                throw new Error('This method must be implemented');
            },

            /**
             * What to do after a save
             */
            postSave: function () {
                this.getRoot().trigger('pim_enrich:form:entity:post_save');

                messenger.notificationFlashMessage(
                    'success',
                    this.updateSuccessMessage
                );
            },

            /**
             * On save fail
             *
             * @param {Object}
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
                        console.log('Errors:', response.responseJSON);
                        this.getRoot().trigger('pim_enrich:form:entity:error:save', response.responseJSON);
                        break;
                    default:
                }

                messenger.notificationFlashMessage(
                    'error',
                    this.updateFailureMessage
                );
            }
        });
    }
);
