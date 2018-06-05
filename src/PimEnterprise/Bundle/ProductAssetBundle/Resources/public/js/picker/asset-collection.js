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
        'backbone/bootstrap-modal'
    ], (
        $,
        _,
        __,
        Backbone,
        template,
        templateModal,
        FetcherRegistry,
        FormBuilder,
        Routing
    ) => {
        return Backbone.View.extend({
            className: 'AknAssetCollectionField',
            data: [],
            context: {},
            template: _.template(template),
            events: {
                'click .add-asset': 'updateAssets',
                'click .asset-thumbnail': 'updateAssetsFromPreview'
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
                this.openPreviewModal(clickEvent).then(function (assets) {
                    this.data = assets;

                    this.trigger('collection:change', assets);
                    this.render();
                }.bind(this));
            },

            /**
             * Opens a modal to show the preview
             *
             * @param {Event} clickEvent
             */
            openPreviewModal(clickEvent) {
                const deferred = $.Deferred();

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
                        removeLabel: __('pimee_product_asset.form.product.asset.remove')
                    });
                    modal.open();

                    const switchModalItem = function (item) {
                        modal.$('.asset-thumbnail-item').addClass('AknAssetCollectionField-listItem--transparent');
                        item.removeClass('AknAssetCollectionField-listItem--transparent');
                        $('.main-preview').attr('src', item.data('url'));
                        $('.buttons').stop(true, true).animate({
                            scrollLeft: item.position().left
                            - ($('.buttons').width() - 140) / 2
                        }, 400);
                        modal.$('.description').html(item.data('description'));
                        modal.$('.download').attr('href', item.data('url'));
                    };

                    const switchWithGap = function (gap, destroy) {
                        let thumbnails = modal.$('.asset-thumbnail-item');
                        let clickedIndex = null;
                        thumbnails.each(function (i, thumbnail) {
                            if (!($(thumbnail).hasClass('AknAssetCollectionField-listItem--transparent'))) {
                                clickedIndex = i;
                            }
                        });
                        if (destroy === true) {
                            $(thumbnails[clickedIndex]).remove();
                            thumbnails = modal.$('.asset-thumbnail-item');
                            if (clickedIndex === 0) {
                                clickedIndex++;
                            }
                        }
                        switchModalItem($(thumbnails[(clickedIndex + gap + thumbnails.length) % thumbnails.length]));
                    };

                    modal.$('.AknAssetCollectionField-listItem').click(function () {
                        switchModalItem($(this));
                    });

                    modal.$('.browse-left').click(function () {
                        switchWithGap(-1, false);
                    });

                    modal.$('.browse-right').click(function () {
                        switchWithGap(1, false);
                    });

                    modal.$('.remove').click(function (e) {
                        e.stopPropagation();
                        switchWithGap(-1, true);
                    });

                    modal.on('cancel', function () {
                        const thumbnails = modal.$('.asset-thumbnail-item');
                        let assetCodes = [];
                        thumbnails.each(function (i, thumbnail) {
                            assetCodes.push($(thumbnail).data('asset'))
                        });
                        modal.close();

                        deferred.resolve(assetCodes);
                    }.bind(this));

                    const clickedAsset = $(clickEvent.currentTarget).closest('.asset-thumbnail-item').data('asset');
                    switchModalItem(modal.$('.asset-thumbnail-item[data-asset="' + clickedAsset + '"]'));
                }.bind(this));

                return deferred.promise();
            }
        });
    }
);
