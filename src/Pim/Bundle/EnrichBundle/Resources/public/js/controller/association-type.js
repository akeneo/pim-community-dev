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
                return FetcherRegistry.getFetcher('association-type').fetch(route.params.code)
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

                        FormBuilder.build(associationType.meta.form)
                            .then(function (form) {
                                form.setData(associationType);
                                form.trigger('pim_enrich:form:entity:post_fetch', associationType);
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
