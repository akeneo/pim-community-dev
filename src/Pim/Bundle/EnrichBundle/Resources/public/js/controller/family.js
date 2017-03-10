'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error',
        'pim/i18n'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error, i18n) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('family').fetch(route.params.code)
                    .then(function (family) {
                        if (!this.active) {
                            return;
                        }

                        var label = _.escape(
                            i18n.getLabel(
                                family.labels,
                                UserContext.get('catalogLocale'),
                                family.code
                            )
                        );

                        PageTitle.set({'family.label': _.escape(label) });

                        FormBuilder.build(family.meta.form)
                            .then(function (form) {
                                form.setData(family);
                                form.trigger('pim_enrich:form:entity:post_fetch', family);
                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
