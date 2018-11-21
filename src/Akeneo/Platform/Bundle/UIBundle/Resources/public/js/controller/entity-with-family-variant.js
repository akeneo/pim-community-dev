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
        'pim/page-title'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher(this.options.config.entity).fetch(route.params.id, {cached: false})
                    .then((product) => {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set(
                            { 'product.label': product.meta.label[UserContext.get('catalogLocale')] }
                        );

                        return FormBuilder.build(product.meta.form)
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(product);

                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
