'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'backbone',
        'oro/datagrid/abstract-action',
        'pim/router',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function(_, __, Backbone, AbstractAction, Router, formBuilder, fetcherRegistry) {
        return AbstractAction.extend({
            form: null,

            /**
             * {@inheritdoc}
             */
            execute: function() {
                const entityCode = this.model.get(this.propertyCode);

                return fetcherRegistry.getFetcher(this.fetcher).fetch(
                    entityCode,
                    {cached: false}
                ).then((entity) => {
                    return formBuilder.build(entity.meta.form)
                        .then((form) => {
                            form.setData(entity);
                            form.trigger('pim_enrich:form:entity:post_fetch', entity);

                            this.listenTo(form, 'cancel', () => {
                                this.modal.trigger('cancel');
                            });

                            this.modal = new Backbone.BootstrapModal({
                                className: 'modal modal--fullPage edit-family-variant-modal',
                                content: '',
                                cancelText: __('pim_enrich.entity.family.variant.cancel'),
                                okText: __('pim_enrich.entity.family.variant.save'),
                                okCloses: false,
                                buttons: false
                            });

                            this.modal.open();

                            form.setElement(this.modal.$('.modal-body')).render();
                        });
                });
            }
        });
    }
);
