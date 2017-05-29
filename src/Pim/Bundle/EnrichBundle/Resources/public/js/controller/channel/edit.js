'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/base',
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
            renderRoute: function (route) {
                if (undefined === route.params.code) {
                    var label = 'pim_enrich.entity.channel.title.create';

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
                    return FetcherRegistry.getFetcher('channel').fetch(route.params.code, {
                        cached: false,
                        generateMissing: true
                    }).then(function (channel) {
                        var label = _.escape(
                            i18n.getLabel(
                                channel.labels,
                                UserContext.get('catalogLocale'),
                                channel.code
                            )
                        );

                        return createForm.call(this, this.$el, channel, label, channel.meta.form);
                    }.bind(this)).fail(function (response) {
                        var message = response.responseJSON ? response.responseJSON.message : __('error.common');

                        var errorView = new Error(message, response.status);
                        errorView.setElement(this.$el).render();
                    });
                }

                function createForm(domElement, channel, label, formExtension) {
                    PageTitle.set({'channel.label': _.escape(label) });

                    return FormBuilder.build(formExtension)
                        .then(function (form) {
                            this.on('pim:controller:can-leave', function (event) {
                                form.trigger('pim_enrich:form:can-leave', event);
                            });
                            form.setData(channel);
                            form.trigger('pim_enrich:form:entity:post_fetch', channel);
                            form.setElement(domElement).render();
                        }.bind(this));
                }
            }
        });
    }
);
