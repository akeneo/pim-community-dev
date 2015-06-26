'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pam/template/picker/asset-grid',
        'oro/datagrid-builder',
        'oro/mediator'
    ],
    function (_, Backbone, BaseForm, template, datagridBuilder, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.datagrid = {
                    name: 'product-asset-grid',
                    paramName: 'assetCodes',
                    getInitialParams: function () {
                        var params = {};
                        params['assetCodes'] = this.getParamValue();

                        return params;
                    },
                    getParamValue: _.bind(function () {
                        return [];
                    }, this),
                    getModelIdentifier: function (model) {
                        return model.get('code');
                    }
                };

                mediator.on('datagrid:selectModel:' + this.datagrid.name, _.bind(this.selectModel, this));
                mediator.on('datagrid:unselectModel:' + this.datagrid.name, _.bind(this.unselectModel, this));
                mediator.on('datagrid_collection_set_after', _.bind(this.updateChecked, this));
                mediator.on('grid_load:complete', _.bind(this.updateChecked, this));
                mediator.on('column_form_listener:initialized', _.bind(function onColumnListenerReady(gridName) {
                    mediator.trigger(
                        'column_form_listener:set_selectors:' + gridName,
                        { included: '#asset-appendfield' }
                    );
                }, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var urlParams    = this.datagrid.getInitialParams();
                urlParams.alias  = this.datagrid.name;
                urlParams.params = this.datagrid.getInitialParams();

                this.$el.html(this.template({}));

                $.get(Routing.generate('pim_datagrid_load', urlParams)).done(_.bind(function (response) {
                    this.$('#grid-' + this.datagrid.name).data(
                        { 'metadata': response.metadata, 'data': JSON.parse(response.data) }
                    );

                    require(response.metadata.requireJSModules, function () {
                        datagridBuilder(_.toArray(arguments));
                    });
                }, this));



                return this.renderExtensions();
            },
            selectModel: function (model) {
                var assets = this.getAssets();
                assets.push(model.get('code'));
                assets = _.uniq(assets);

                this.setAssets(assets);
            },
            unselectModel: function (model) {
                var assets = _.without(this.getAssets(), model.get('code'));

                this.setAssets(assets);
            },
            getAssets: function () {
                var assets = this.$('#asset-appendfield').val();

                return '' === assets ? [] : assets.split(',');
            },
            setAssets: function (assetCodes) {
                this.$('#asset-appendfield').val(assetCodes.join(','));

                return this;
            },
            updateChecked: function (datagrid) {
                var assets = this.getAssets();

                _.each(datagrid.models, _.bind(function (row) {
                    if (_.contains(assets, row.get('code'))) {
                        row.set('is_checked', true);
                    }
                }, this));

                this.setAssets(assets);
            }
        });
    }
);
