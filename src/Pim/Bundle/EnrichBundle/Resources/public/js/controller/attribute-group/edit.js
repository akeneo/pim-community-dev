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
        'pim/error'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('attribute-group').fetch(route.params.identifier, {cached: false})
                    .then(function (attributeGroup) {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({'attribute group.identifier': _.escape(attributeGroup.labels[UserContext.get('catalogLocale')]) });

                        FormBuilder.build('pim-attribute-group-edit-form')
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(attributeGroup);

                                form.trigger('pim_enrich:form:entity:post_fetch', attributeGroup);

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
