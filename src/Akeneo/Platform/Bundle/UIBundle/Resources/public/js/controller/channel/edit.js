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
                FetcherRegistry.getFetcher('locale').clear();
                if (undefined === route.params.code) {
                    var label = 'pim_enrich.entity.channel.label.create';

                    return createForm.call(
                        this,
                        this.$el,
                        {
                            'code': '',
                            'currencies': [],
                            'locales': [],
                            'category_tree': '',
                            'conversion_units': [],
                            'labels': {},
                            'meta': {}
                        },
                        label,
                        'pim-channel-create-form'
                    );
                } else {
                    return FetcherRegistry.getFetcher('channel')
                        .fetch(route.params.code, {cached: false, 'filter_locales': 0})
                        .then((channel) => {
                            const label = _.escape(
                                i18n.getLabel(
                                    channel.labels,
                                    UserContext.get('catalogLocale'),
                                    channel.code
                                )
                            );

                            return createForm.call(this, this.$el, channel, label, channel.meta.form);
                        });
                }

                function createForm(domElement, channel, label, formExtension) {
                    PageTitle.set({'channel.label': _.escape(label)});

                    return FormBuilder.build(formExtension)
                        .then((form) => {
                            this.on('pim:controller:can-leave', function (event) {
                                form.trigger('pim_enrich:form:can-leave', event);
                            });
                            form.setData(channel);
                            form.trigger('pim_enrich:form:entity:post_fetch', channel);
                            form.setElement(domElement).render();

                            return form;
                        });
                }
            }
        });
    }
);
