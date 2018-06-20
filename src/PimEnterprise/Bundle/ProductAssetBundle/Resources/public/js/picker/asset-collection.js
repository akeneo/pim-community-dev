'use strict';

/**
 * Asset collection field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pimee/template/picker/asset-collection',
        'pimee/template/picker/asset-collection-preview',
        'pim/fetcher-registry',
        'pim/form-builder',
        'routing',
        'backbone/bootstrap-modal',
        'pim/security-context'
    ], (
        $,
        _,
        __,
        Backbone,
        template,
        templateModal,
        FetcherRegistry,
        FormBuilder,
        Routing,
        BootstrapModal,
        SecurityContext
    ) => {
        return Backbone.View.extend({
            className: 'AknAssetCollectionField',
            data: [],
            context: {},
            template: _.template(template),
            events: {
                'click .add-asset': 'updateAssets',
                'click .asset-thumbnail-item': 'updateAssetsFromPreview'
            },
            modalTemplate: _.template(templateModal),

            /**
             * {@inheritdoc}
             *
             * In the case where asset codes are integers, even if their order iscorrectly managed by the backend, the
             * fetcher will reorganize them, sorting them by code ascending. As "this.data" contains the codes in the
             * correct order, we reorder the assets according to this list of code.
             */
            render() {
                FetcherRegistry.getFetcher('asset').fetchByIdentifiers(this.data).then(assets => {
                    let orderedAssets = [];
                    this.data.forEach(assetCode => {
                        orderedAssets = orderedAssets.concat(assets.filter(asset => asset.code === assetCode));
                    });

                    this.$el.html(this.template({
                        assets: orderedAssets,
                        locale: this.context.locale,
                        scope: this.context.scope,
                        thumbnailFilter: 'thumbnail',
                        editMode: this.context.editMode
                    }));

                    if ('view' !== this.context.editMode) {
                        this.$('.AknAssetCollectionField-list').sortable({
                            update: this.updateDataFromDom.bind(this)
                        });
                    }

                    this.delegateEvents();
                });

                return this;
            },

            /**
             *
             */
            updateDataFromDom() {
                const assets = this.$('.AknAssetCollectionField-listItem')
                    .map((index, listItem) => listItem.dataset.asset)
                    .get();

                this.data = assets;
                this.trigger('collection:change', assets);
            },

            /**
             * Set data into the view
             *
             * @param {Array} data
             */
            setData(data) {
                this.data = data;
            },

            /**
             * Set context into the view
             *
             * @param {Object} context
             */
            setContext(context) {
                this.context = context;
            },

            /**
             * Launch the asset picker and set the assets after update
             */
            updateAssets() {
                this.manageAssets().then(assets => {
                    this.data = assets;

                    this.trigger('collection:change', assets);
                    this.render();
                });
            },

            /**
             * Launch the asset picker
             *
             * @return {Promise}
             */
            manageAssets() {
                const deferred = $.Deferred();

                FormBuilder.build('pimee-product-asset-picker-form').then(form => {
                    let modal = new Backbone.BootstrapModal({
                        className: 'modal modal--fullPage modal--topButton',
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        title: '',
                        content: '',
                        cancelText: ' ',
                        okText: __('confirmation.title')
                    });
                    modal.open();

                    form.setImagePathMethod(function (item) {
                        return Routing.generate('pimee_product_asset_thumbnail', {
                            code: item.code,
                            filter: 'thumbnail',
                            channelCode: this.getScope(),
                            localeCode: this.getLocale()
                        });
                    });

                    form.setLabelMethod(item => item.description);

                    form.setElement(modal.$('.modal-body'))
                        .render()
                        .setItems(this.data);

                    modal.on('cancel', deferred.reject);
                    modal.on('ok', () => {
                        const assets = _.sortBy(form.getItems(), 'code');
                        modal.close();

                        deferred.resolve(assets);
                    });
                });

                return deferred.promise();
            },

            /**
             * Launch the asset picker and set the assets after update
             *
             * @param {Event} clickEvent
             */
            updateAssetsFromPreview: function (clickEvent) {
                const currentAssetCode = $(clickEvent.currentTarget).closest('.asset-thumbnail-item').data('asset');

                this.openPreviewModal(currentAssetCode).then(function (assets) {
                    this.data = assets;

                    this.trigger('collection:change', assets);
                    this.render();
                }.bind(this));
            },

            /**
             * Opens a modal to show the preview
             *
             * @param {Event} currentAssetCode
             */
            openPreviewModal(currentAssetCode) {
                const deferred = $.Deferred();
                const editMode = this.context.editMode;
                const aclGranted = SecurityContext.isGranted('pimee_product_asset_remove_from_collection');

                FetcherRegistry.getFetcher('asset').fetchByIdentifiers(this.data).then(function (assets) {
                    const modal = new Backbone.BootstrapModal({
                        className: 'modal modal--fullPage modal--topButton',
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        template: this.modalTemplate,
                        assets: assets,
                        locale: this.context.locale,
                        scope: this.context.scope,
                        content: '',
                        thumbnailFilter: 'thumbnail',
                        assetCollectionPreviewTitle: __('pimee_product_asset.form.product.asset.preview_title'),
                        downloadLabel: __('pimee_product_asset.form.product.asset.download'),
                        removeLabel: __('pimee_product_asset.form.product.asset.remove'),
                        yesLabel: __('pimee_product_asset.form.product.asset.yes'),
                        noLabel: __('pimee_product_asset.form.product.asset.no'),
                        confirmLabel: __('pimee_product_asset.form.product.asset.assetRemoveConfirmationLabel'),
                        canRemoveAsset: aclGranted && 'view' !== editMode
                    });
                    console.log('editMode', editMode)
                    modal.open();

                    const navigateToItem = function (assetThumbnail) {
                        modal.$('.asset-thumbnail-item').addClass('AknAssetCollectionField-listItem--transparent');
                        assetThumbnail.removeClass('AknAssetCollectionField-listItem--transparent');
                        modal.$('.main-preview').attr('src', '');
                        modal.$('.main-preview').attr('src', assetThumbnail.data('url'));
                        modal.$('.buttons').stop(true, true).animate({
                            scrollLeft: assetThumbnail.position().left
                            - (modal.$('.buttons').width() - 140) / 2
                        }, 400);
                        modal.$('.description').html(assetThumbnail.data('description'));
                        modal.$('.download').attr('href', assetThumbnail.data('url'));
                    };
                    const navigateToNeighbor = function (side, isCurrentElementDestroyed) {
                        let thumbnails = modal.$('.asset-thumbnail-item');
                        let clickedIndex = null;
                        thumbnails.each(function (i, thumbnail) {
                            if (!($(thumbnail).hasClass('AknAssetCollectionField-listItem--transparent'))) {
                                clickedIndex = i;
                            }
                        });
                        if (isCurrentElementDestroyed === true) {
                            $(thumbnails[clickedIndex]).remove();
                            thumbnails = modal.$('.asset-thumbnail-item');
                            if (clickedIndex === 0) {
                                clickedIndex++;
                            }
                        }
                        navigateToItem($(thumbnails[(clickedIndex + side + thumbnails.length) % thumbnails.length]));
                    };

                    const toggleRemoveConfirmation = (show) => {
                        const hiddenClass = 'AknButtonList--hide'
                        if (show) {
                            modal.$('.remove-confirmation').removeClass(hiddenClass);
                        } else {
                            modal.$('.remove-confirmation').addClass(hiddenClass);
                        }
                    };

                    modal.$('.AknAssetCollectionField-listItem').click(function () {
                        navigateToItem($(this));
                    });

                    modal.$('.browse-left').click(function () {
                        navigateToNeighbor(-1, false);
                    });

                    modal.$('.browse-right').click(function () {
                        navigateToNeighbor(1, false);
                    });

                    modal.$('.remove').click((e) => {
                        e.stopPropagation();
                        toggleRemoveConfirmation(true)
                    });

                    modal.$('.remove-confirmation .close').on('click', (e) => {
                        e.stopPropagation();
                        toggleRemoveConfirmation(false)
                    })

                    modal.$('.remove-confirmation .confirm').on('click', (e) => {
                        e.stopPropagation();
                        navigateToNeighbor(-1, true);
                        toggleRemoveConfirmation(false)
                    })

                    modal.on('cancel', function () {
                        const thumbnails = modal.$('.asset-thumbnail-item');
                        let assetCodes = [];
                        thumbnails.each(function (i, thumbnail) {
                            assetCodes.push($(thumbnail).data('asset'))
                        });
                        modal.close();

                        deferred.resolve(assetCodes);
                    }.bind(this));

                    navigateToItem(modal.$('.asset-thumbnail-item[data-asset="' + currentAssetCode + '"]'));
                }.bind(this));

                return deferred.promise();
            }
        });
    }
);
