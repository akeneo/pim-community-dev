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
                'click .add-asset': 'updateAssets'
            },

            /**
             * {@inheritdoc}
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

                    this.$('.AknAssetCollectionField-list').sortable({
                        update: this.updateDataFromDom.bind(this)
                    });

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
            }
        });
    }
);
