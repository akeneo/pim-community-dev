'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function(_, Backbone, formBuilder, fetcherRegistry) {
        return {
            /**
             * Create a modal from fetcher and entity identifier
             *
             * @param {String} entityCode
             * @param {String} fetcherCode
             *
             * @return {Backbone.Modal}
             */
            createModal: function(entityCode, fetcherCode) {
                return fetcherRegistry.getFetcher(fetcherCode).fetch(
                    entityCode,
                    {cached: false}
                ).then((entity) => {
                    return formBuilder.build(entity.meta.form)
                        .then((form) => {
                            const modal = new Backbone.BootstrapModal({
                                className: 'modal modal--fullPage',
                                content: '',
                                okCloses: false,
                                buttons: false
                            });

                            form.setData(entity);
                            form.trigger('pim_enrich:form:entity:post_fetch', entity);

                            form.on('cancel', () => {
                                modal.trigger('cancel');
                            });
                            form.on('pim_enrich:form:entity:post_save', () => {
                                modal.trigger('cancel');
                            });

                            modal.open();

                            form.setElement(modal.$('.modal-body')).render();

                            return modal;
                        });
                });
            }
        }
    }
);
