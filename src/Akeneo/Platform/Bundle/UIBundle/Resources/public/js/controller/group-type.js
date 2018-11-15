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
        'pim/i18n'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, i18n) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('group-type').fetch(route.params.code, {cached: false})
                    .then((groupType) => {
                        if (!this.active) {
                            return;
                        }

                        var label = _.escape(
                            i18n.getLabel(
                                groupType.labels,
                                UserContext.get('catalogLocale'),
                                groupType.code
                            )
                        );

                        PageTitle.set({'group type.label': _.escape(label) });

                        return FormBuilder.build('pim-group-type-edit-form')
                            .then((form) => {
                                this.on('pim:controller:can-leave', (event) => {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(groupType);
                                form.trigger('pim_enrich:form:entity:post_fetch', groupType);
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
