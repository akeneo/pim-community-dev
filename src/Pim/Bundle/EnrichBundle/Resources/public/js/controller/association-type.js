'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error',
        'pim/i18n'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error, i18n) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('association-type').fetch(route.params.code, {cached: false})
                    .then(function (associationType) {
                        if (!this.active) {
                            return;
                        }

                        var label = _.escape(
                            i18n.getLabel(
                                associationType.labels,
                                UserContext.get('catalogLocale'),
                                associationType.code
                            )
                        );

                        PageTitle.set({'association type.label': _.escape(label) });

                        return FormBuilder.build(associationType.meta.form)
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(associationType);
                                form.trigger('pim_enrich:form:entity:post_fetch', associationType);
                                form.setElement(this.$el).render();

                                return form;
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var message = response.responseJSON ? response.responseJSON.message : __('error.common');

                    var errorView = new Error(message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
