'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pimee/template/picker/asset-grid',
        'text!pimee/template/picker/basket',
        'oro/datagrid-builder',
        'oro/mediator',
        'pim/fetcher-registry'
    ],
    function (_, Backbone, BaseForm, template, basketTemplate, datagridBuilder, mediator, FetcherRegistry) {
        return BaseForm.extend({
            template: _.template(template),
            basketTemplate: _.template(basketTemplate),
            events: {
                'click .remove-asset': 'removeAssetFromBasket'
            },
            initialize: function () {
                this.datagridModel = null;

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.datagrid = {
                    name: 'product-asset-grid',
                    paramName: 'assetCodes'
                };

                mediator.on('datagrid:selectModel:' + this.datagrid.name, _.bind(this.selectModel, this));
                mediator.on('datagrid:unselectModel:' + this.datagrid.name, _.bind(this.unselectModel, this));
                mediator.on('datagrid_collection_set_after', _.bind(this.updateChecked, this));
                mediator.on('datagrid_collection_set_after', _.bind(this.setDatagrid, this));
                mediator.on('grid_load:complete', _.bind(this.updateChecked, this));
                mediator.once('column_form_listener:initialized', _.bind(function onColumnListenerReady(gridName) {
                    if (!this.configured) {
                        mediator.trigger(
                            'column_form_listener:set_selectors:' + gridName,
                            { included: '#asset-appendfield' }
                        );
                    }
                }, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({}));
                this.renderGrid(this.datagrid);

                return this.renderExtensions();
            },
            renderGrid: function () {
                var urlParams = {
                    alias: this.datagrid.name,
                    params: {}
                };

                $.get(Routing.generate('pim_datagrid_load', urlParams)).done(_.bind(function (response) {
                    this.$('#grid-' + this.datagrid.name).data(
                        { 'metadata': response.metadata, 'data': JSON.parse(response.data) }
                    );

                    require(response.metadata.requireJSModules, function () {
                        datagridBuilder(_.toArray(arguments));
                    });

                }, this));
            },
            setDatagrid: function (datagridModel) {
                this.datagridModel = datagridModel;
            },
            selectModel: function (model) {
                this.addAsset(model.get('code'));
            },
            unselectModel: function (model) {
                this.removeAsset(model.get('code'));
            },
            addAsset: function (code) {
                var assets = this.getAssets();
                assets.push(code);
                assets = _.uniq(assets);

                this.setAssets(assets);
            },
            removeAsset: function (code) {
                var assets = _.without(this.getAssets(), code);

                this.setAssets(assets);
            },
            getAssets: function () {
                console.log(this.$('#asset-appendfield').val());
                var assets = $('#asset-appendfield').val();

                return '' === assets ? [] : assets.split(',');
            },
            setAssets: function (assetCodes) {
                $('#asset-appendfield').val(assetCodes.join(','));
                this.updateBasket();

                return this;
            },
            updateChecked: function (datagrid) {
                var assets = this.getAssets();

                _.each(datagrid.models, _.bind(function (row) {
                    if (_.contains(assets, row.get('code'))) {
                        row.set('is_checked', true);
                    } else {
                        row.set('is_checked', null);
                    }
                }, this));

                this.setAssets(assets);
            },
            removeAssetFromBasket: function (event) {
                this.removeAsset(event.currentTarget.dataset.asset);
                if (this.datagridModel) {
                    this.updateChecked(this.datagridModel);
                }
            },
            updateBasket: function () {
                var assetCodes = this.getAssets();

                FetcherRegistry.getFetcher('asset').fetchByIdentifiers(this.getAssets()).then(_.bind(function (assets) {
                    assets = _.map(assetCodes, function (assetCode) {
                        return _.findWhere(assets, {code: assetCode});
                    });

                    this.$('.basket').html(this.basketTemplate({assets: assets}));
                    this.delegateEvents();
                }, this));
            }
        });
    }
);
