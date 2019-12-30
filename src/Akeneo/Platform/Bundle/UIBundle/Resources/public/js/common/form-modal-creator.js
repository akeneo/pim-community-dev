'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'pim/template/common/modal-centered'
    ],
    function(
        _,
        __,
        Backbone,
        formBuilder,
        fetcherRegistry,
        UserContext,
        i18n,
        modalTemplate
    ) {
        return {
            /**
             * Create a modal from fetcher and entity identifier
             *
             * @param {String} entityCode
             * @param {String} fetcherCode
             *
             * @return {Promise}
             */
            createModal: function(entityCode, fetcherCode) {
                return fetcherRegistry.getFetcher(fetcherCode).fetch(
                    entityCode,
                    {cached: false}
                ).then((entity) => {
                    return formBuilder.build(entity.meta.form)
                        .then((form) => {
                            form.setData(entity);
                            form.trigger('pim_enrich:form:entity:post_fetch', entity);
                            form.on('pim_enrich:form:entity:post_save', () => {
                                modal.trigger('cancel');
                            });

                            const familyVariant = entity;
                            const modal = new Backbone.BootstrapModal({
                                content: form,
                                buttons: false,
                                title: i18n.getLabel(
                                    familyVariant.labels,
                                    UserContext.get('catalogLocale'),
                                    familyVariant.code
                                ),
                                subtitle: __('Code')  + ': ' + familyVariant.code,
                                template: _.template(modalTemplate),
                                okText: '',
                                innerClassName: 'AknFullPage--full',
                            });
                            modal.open();

                            return modal;
                        });
                });
            }
        }
    }
);
