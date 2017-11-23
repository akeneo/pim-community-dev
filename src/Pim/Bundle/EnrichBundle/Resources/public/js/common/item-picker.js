'use strict';

/**
 * Extension to display full screen item picker to choose elements from a grid
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'pim/form',
        'pim/template/common/item-picker',
        'pimee/template/picker/basket',
        'oro/datagrid-builder',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/user-context',
        'oro/datafilter/product_category-filter',
        'require-context',
        'pim/menu/resizable'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        BaseForm,
        template,
        basketTemplate,
        datagridBuilder,
        mediator,
        FetcherRegistry,
        UserContext,
        CategoryFilter,
        requireContext,
        Resizable
    ) {
        return BaseForm.extend({
            template: _.template(template),
            basketTemplate: _.template(basketTemplate),
            events: {
                'click .remove-asset': 'removeAssetFromBasket'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.datagridModel = null;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.datagrid = {
                    name: 'asset-picker-grid',
                    paramName: 'assetCodes'
                };

                mediator.on('datagrid:selectModel:' + this.datagrid.name, this.selectModel.bind(this));
                mediator.on('datagrid:unselectModel:' + this.datagrid.name, this.unselectModel.bind(this));
                mediator.on('datagrid_collection_set_after', this.updateChecked.bind(this));
                mediator.on('datagrid_collection_set_after', this.setDatagrid.bind(this));
                mediator.on('grid_load:complete', this.updateChecked.bind(this));
                mediator.once('column_form_listener:initialized', function onColumnListenerReady(gridName) {
                    if (!this.configured) {
                        mediator.trigger(
                            'column_form_listener:set_selectors:' + gridName,
                            { included: '#asset-appendfield' }
                        );
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({
                    title: __('pimee_product_asset.form.product.asset.title'),
                    description: __('pimee_product_asset.form.product.asset.description'),
                    locale: this.getLocale()
                }));

                this.renderGrid(this.datagrid);
                this.setupResizableColumn();

                return this.renderExtensions();
            },

            /**
             * Make the categories tree resizable. Because of flexbox we get the
             * rendered width of the column and use that as the minimum.
             */
            setupResizableColumn() {
                const resizableColumn = this.$('.ui-resizable-container--column');
                const originalColumnWidth = resizableColumn.outerWidth();

                Resizable.set({
                    minWidth: originalColumnWidth,
                    maxWidth: 500,
                    container: this.$('.ui-resizable-container--column-child'),
                    storageKey: 'asset-grid'
                });
            },

            /**
             * {@inheritdoc}
             */
            shutdown() {
                Resizable.destroy();

                return BaseForm.prototype.shutdown.apply(this, arguments);
            },

            /**
             * Render the asset grid
             */
            renderGrid: function () {
                const urlParams = {
                    alias: this.datagrid.name,
                    params: {
                        dataLocale: this.getLocale(),
                        _filter: {
                            category: { value: { categoryId: -2 }}, // -2 = all categories
                            scope: { value: this.getScope() }
                        }
                    }
                };

                /* jshint nonew: false */
                new CategoryFilter(urlParams, 'asset-grid', 'pimee_asset_picker_categorytree', '#asset-tree');

                $.get(Routing.generate('pim_datagrid_load', urlParams)).done(function (response) {
                    this.$('#grid-' + this.datagrid.name).data(
                        { 'metadata': response.metadata, 'data': JSON.parse(response.data) }
                    );

                    let resolvedModules = [];
                    response.metadata.requireJSModules.concat(['oro/datagrid/pagination-input'])
                        .forEach(function(module) {
                            resolvedModules.push(requireContext(module))
                        });

                    datagridBuilder(resolvedModules);
                }.bind(this));
            },

            /**
             * Triggered by the event 'datagrid_collection_set_after' to keep a locale reference to
             * the grid model #gridCrap
             *
             * @param {Object} datagridModel
             */
            setDatagrid: function (datagridModel) {
                this.datagridModel = datagridModel;
            },

            /**
             * Triggered by the datagrid:selectModel:asset-picker-grid event
             *
             * @param {Object} model
             */
            selectModel: function (model) {
                this.addAsset(model.get('code'));
            },

            /**
             * Triggered by the datagrid:unselectModel:asset-picker-grid event
             *
             * @param {Object} model
             */
            unselectModel: function (model) {
                this.removeAsset(model.get('code'));
            },

            /**
             * Add an asset to the basket
             *
             * @param {string} code
             *
             * @return this
             */
            addAsset: function (code) {
                let assets = this.getAssets();
                assets.push(code);
                assets = _.uniq(assets);

                this.setAssets(assets);

                return this;
            },

            /**
             * Remove an asset from the collection
             *
             * @param {string} code
             *
             * @return this
             */
            removeAsset: function (code) {
                let assets = _.without(this.getAssets(), code);

                this.setAssets(assets);

                return this;
            },

            /**
             * Get all assets in the collection
             *
             * @return {Array}
             */
            getAssets: function () {
                const assets = $('#asset-appendfield').val();

                return (!_.isUndefined(assets) && '' !== assets) ? assets.split(',') : [];
            },

            /**
             * Set assets
             *
             * @param {Array} assetCodes
             *
             * @return this
             */
            setAssets: function (assetCodes) {
                $('#asset-appendfield').val(assetCodes.join(','));
                this.updateBasket();

                return this;
            },

            /**
             * Update the checked rows in the grid according to the current model
             *
             * @param {Object} datagrid
             */
            updateChecked: function (datagrid) {
                if (datagrid.inputName !== this.datagrid.name) {
                    return;
                }

                const assets = this.getAssets();

                _.each(datagrid.models, function (row) {
                    if (_.contains(assets, row.get('code'))) {
                        row.set('is_checked', true);
                    } else {
                        row.set('is_checked', null);
                    }
                }.bind(this));

                this.setAssets(assets);
            },

            /**
             * Remove an asset from the basket (triggered by 'click .remove-asset')
             *
             * @param {Event} event
             */
            removeAssetFromBasket: function (event) {
                this.removeAsset(event.currentTarget.dataset.asset);
                if (this.datagridModel) {
                    this.updateChecked(this.datagridModel);
                }
            },

            /**
             * Render the basket to update its content
             */
            updateBasket: function () {
                FetcherRegistry.getFetcher('asset').fetchByIdentifiers(this.getAssets())
                    .then(function (assets) {
                        this.$('.basket').html(this.basketTemplate({
                            assets: assets,
                            thumbnailFilter: 'thumbnail',
                            scope: this.getScope(),
                            locale: this.getLocale()
                        }));

                        this.delegateEvents();
                    }.bind(this));
            },

            /**
             * Get the current locale
             *
             * @return {string}
             */
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },

            /**
             * Get the current scope
             *
             * @return {string}
             */
            getScope: function () {
                return UserContext.get('catalogScope');
            }
        });
    }
);
