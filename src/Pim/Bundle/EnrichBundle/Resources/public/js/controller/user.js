'use strict';

define([
        'underscore',
        'pim/controller/front',
        'pim/fetcher-registry',
        'pim/page-title',
        'pim/form-builder'
    ], function (
        _,
        BaseController,
        FetcherRegistry,
        PageTitle,
        FormBuilder
    ) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('user').fetch(
                    route.params.code,
                    //{cached: false, full_attributes: false}
                ).then((user) => {
                    if (!this.active) {
                        return;
                    }

                    PageTitle.set({'user.username': _.escape(user.username)});

                    return FormBuilder.build(user.meta.form)
                        .then((form) => {
                            this.on('pim:controller:can-leave', function (event) {
                                form.trigger('pim_enrich:form:can-leave', event);
                            });
                            form.setData(user);
                            form.trigger('pim_enrich:form:entity:post_fetch', user);
                            form.setElement(this.$el).render();

                            return form;
                        });
                });
            }
        });
    }
)
