'use strict';
/**
 * Mass associate
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator',
        'routing',
        'pim/mass-edit-form/product/operation',
        'pim/user-context',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/media-url-generator',
        'oro/loading-mask',
        'oro/messenger',
        'pim/template/mass-edit/product/associate/pick',
        'pim/template/mass-edit/product/associate/confirm',
        'pim/template/common/modal-centered'
    ],
    function (
        $,
        _,
        Backbone,
        __,
        Routing,
        BaseOperation,
        UserContext,
        FormBuilder,
        FetcherRegistry,
        i18n,
        MediaUrlGenerator,
        LoadingMask,
        messenger,
        pickTemplate,
        confirmTemplate,
        modalTemplate
    ) {
        return BaseOperation.extend({
            className: 'AknGridToolbar',
            pickTemplate: _.template(pickTemplate),
            confirmTemplate: _.template(confirmTemplate),
            modalTemplate: _.template(modalTemplate),
            errors: null,
            formPromise: null,
            events: {
                'click .associations-list li': 'changeAssociationType',
                'click .add-associations': 'addAssociations'
            },

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue([]);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.readOnly) {
                    this.loadAssociationTypes().then((associationTypes) => {
                        const currentAssociationTypeCode = associationTypes.length ?
                            _.first(associationTypes).code :
                            null;

                        if (null === this.getCurrentAssociationTypeCode() ||
                            undefined === associationTypes.find(
                                associationType => associationType.code === this.getCurrentAssociationTypeCode()
                            )
                        ) {
                            this.setCurrentAssociationType(currentAssociationTypeCode);
                        }

                        this.$el.html(this.pickTemplate({
                            associationTypes,
                            associationType: associationTypes.find(
                                associationType => associationType.code === this.getCurrentAssociationTypeCode()
                            ),
                            locale: UserContext.get('uiLocale'),
                            i18n,
                            label: __('pim_enrich.entity.product.module.associations.association_type_selector'),
                            addAssociationsLabel: __('pim_enrich.entity.product.module.associations.add_associations')
                        }));

                        this.resizeContent(associationTypes.length);

                        this.delegateEvents();
                    });
                } else {
                    var loadingMask = new LoadingMask();
                    this.$el.empty().append(loadingMask.render().$el.show());
                    $.when(
                        FetcherRegistry.getFetcher('product-model').fetchByIdentifiers(
                            this.getValue()[this.getCurrentAssociationTypeCode()].product_models
                        ),
                        FetcherRegistry.getFetcher('product').fetchByIdentifiers(
                            this.getValue()[this.getCurrentAssociationTypeCode()].products
                        )
                    ).then((productModels, products) => {
                        const items = products.concat(productModels);
                        this.$el.html(this.confirmTemplate({
                            items: items,
                            title: __('pim_enrich.entity.product.module.basket.title'),
                            emptyLabel: __('pim_enrich.entity.product.module.basket.empty_basket'),
                            confirmLabel: __(
                                'pim_enrich.mass_edit.product.operation.associate_to_product_and_product_model.confirm'
                            ),
                            imagePathMethod: this.imagePathMethod.bind(this),
                            labelMethod: this.labelMethod.bind(this),
                            readOnly: this.readOnly
                        }));
                        this.delegateEvents();
                    })
                    .always(() => {
                        loadingMask.remove();
                    });
                }
            },

            /**
             * {@inheritdoc}
             */
            imagePathMethod: function (item) {
                let filePath = null;
                if (item.meta.image !== null) {
                    filePath = item.meta.image.filePath;
                }

                return MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small');
            },

            /**
             * {@inheritdoc}
             */
            labelMethod: function (item) {
                return item.meta.label[UserContext.get('catalogLocale')];
            },

            /**
             * Update the model after dom event triggered
             *
             * @param {string} group
             */
            setValue: function (values) {
                const data = this.getFormData();
                data.actions = [{
                    field: 'associations',
                    value: values
                }];

                this.setData(data);
            },

            /**
             * Get current value from mass edit model
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.first(this.getFormData().actions);

                return action ? action.value : null;
            },

            /**
             * Switch the current association type
             *
             * @param {Event} event
             */
            changeAssociationType: function (event) {
                event.preventDefault();
                const associationType = event.currentTarget.dataset.associationType;
                this.setCurrentAssociationType(associationType);

                this.render();
            },

            /**
             * @param {string} associationType
             */
            setCurrentAssociationType: function (associationType) {
                sessionStorage.setItem('current_association_type', associationType);
            },

            /**
             * @returns {string}
             */
            getCurrentAssociationTypeCode: function () {
                return sessionStorage.getItem('current_association_type');
            },

            /**
             * Fetch all the association types
             *
             * @returns {Promise}
             */
            loadAssociationTypes: function () {
                return FetcherRegistry.getFetcher('association-type').fetchAll();
            },

            /**
             * Opens the panel to select new products to associate
             */
            addAssociations: function () {
                this.manageProducts().then((productAndProductModelIdentifiers) => {
                    let productIds = [];
                    let productModelIds = [];
                    productAndProductModelIdentifiers.forEach((item) => {
                        const matchProductModel = item.match(/^product_model_(.*)$/);
                        if (matchProductModel) {
                            productModelIds.push(matchProductModel[1]);
                        } else {
                            const matchProduct = item.match(/^product_(.*)$/);
                            productIds.push(matchProduct[1]);
                        }
                    });

                    const assocType = this.getCurrentAssociationTypeCode();

                    const associations = {};
                    associations[assocType] = {
                        'products': productIds,
                        'product_models': productModelIds,
                        'groups': []
                    };
                    this.setValue(associations);

                    this.getRoot().trigger('mass-edit:navigate:action', 'confirm');
                });
            },

            /**
             * Launch the association product picker
             *
             * @return {Promise}
             */
            manageProducts: function () {
                const deferred = $.Deferred();

                FormBuilder.build('pim-associations-product-picker-form').then((form) => {
                    FetcherRegistry
                        .getFetcher('association-type')
                        .fetch(this.getCurrentAssociationTypeCode())
                        .then((associationType) => {
                            let modal = new Backbone.BootstrapModal({
                                modalOptions: {
                                    backdrop: 'static',
                                    keyboard: false
                                },
                                innerDescription:
                                __('pim_enrich.entity.product.module.associations.manage_description'),
                                okCloses: false,
                                title: __('pim_enrich.entity.product.module.associations.manage', {
                                    associationType: associationType.labels[UserContext.get('catalogLocale')]
                                }),
                                content: '',
                                cancelText: ' ',
                                okText: __('pim_common.confirm'),
                                template: this.modalTemplate,
                                innerClassName: 'AknFullPage--full',
                            });
                            modal.open();
                            modal.on('cancel', deferred.reject);
                            modal.on('ok', () => {
                                const products = form.getItems().sort((a, b) => {
                                    return a.code < b.code;
                                });
                                modal.close();

                                deferred.resolve(products);
                            });

                            form.setElement(modal.$('.modal-body')).render();
                        });
                });

                return deferred.promise();
            },

            /**
             * Validate the model before confirmation
             *
             * @return {Promise}
             */
            validate: function () {
                const deferred = $.Deferred();

                if (_.isEmpty(this.getValue())) {
                    messenger.notify(
                        'error',
                        __('pim_enrich.mass_edit.product.operation.associate_to_product_and_product_model.validate')
                    );

                    deferred.resolve(false);
                } else {
                    deferred.resolve(true);
                }

                return deferred;
            },

            /**
             * The dropdown and the mass edit layout have incompatible CSS displays.
             * We need to trick the height of the content box to avoid a too small zone to click.
             * The height is estimated from the count of association types.
             */
            resizeContent: function (associationTypesLength) {
                const contentDom = $('.AknFullPage-content.AknFullPage-content--withIllustration');
                const headerHeight = $('.AknFullPage-titleContainer').outerHeight();
                const newHeight = (headerHeight + associationTypesLength * 26 + 150);
                contentDom.css('min-height', Math.min(newHeight, $('body').outerHeight()) + 'px');
            }
        });
    }
);
