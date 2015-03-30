'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/tab/associations',
        'pim/association-manager',
        'pim/attribute-manager',
        'pim/user-context',
        'routing',
        'oro/mediator',
        'oro/datagrid-builder'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        formTemplate,
        AssociationManager,
        AttributeManager,
        UserContext,
        Routing,
        mediator,
        datagridBuilder
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tab-pane active',
            id: 'product-associations',
            events: {
                'click .nav-tabs li':                'changeAssociationType',
                'click #association-buttons button': 'changeAssociationTargets'
            },
            initialize: function () {
                this.state = new Backbone.Model({
                    associationTarget: 'products'
                });

                this.datagrids = {
                    products: {
                        name: 'association-product-grid',
                        getInitialParams: _.bind(function (associationType) {
                            var params = {
                                product: this.getRoot().model.get('meta').id
                            };
                            params[this.datagrids.products.paramName] = this.datagrids.products.getParamValue(associationType);
                            params.dataLocale = UserContext.getUserContext().get('catalogLocale');

                            return params;
                        }, this),
                        paramName: 'associationType',
                        getParamValue: _.bind(function (associationType) {
                            return _.findWhere(this.state.get('associationTypes'), {code: associationType}).id;
                        }, this),
                        getModelIdentifier: function (model, identifierAttribute) {
                            return model.get(identifierAttribute.code);
                        }
                    },
                    groups: {
                        name: 'association-group-grid',
                        getInitialParams: function (associationType) {
                            var params = {};
                            params[this.paramName] = this.getParamValue(associationType);

                            return params;
                        },
                        paramName: 'associatedIds',
                        getParamValue: _.bind(function (associationType) {
                            var associations = this.getRoot().model.get('meta').associations;
                            return associations[associationType] ? associations[associationType].groupIds : [];
                        }, this),
                        getModelIdentifier: function (model) {
                            return model.get('code');
                        }
                    }
                };

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('associations', _.__('pim_enrich.form.product.tab.associations.title'));

                _.each(this.datagrids, _.bind(function (datagrid) {
                    mediator.on('datagrid:selectModel:' + datagrid.name, _.bind(function (model) {
                        this.selectModel(model, datagrid);
                    }, this));

                    mediator.on('datagrid:unselectModel:' + datagrid.name, _.bind(function (model) {
                        this.unselectModel(model, datagrid);
                    }, this));
                }, this));

                mediator.on('post_save', _.bind(function () {
                    this.$('.selection-inputs input').val('');
                    var associationType =  this.state.get('currentAssociationType');
                    _.each(this.datagrids, function (datagrid) {
                        if ($('#grid-' + datagrid.name).length) {
                            mediator
                                .trigger('datagrid:setParam:' + datagrid.name, datagrid.paramName, datagrid.getParamValue(associationType))
                                .trigger('datagrid:doRefresh:' + datagrid.name);
                        }
                    });
                }, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return;
                }

                $.when(
                    this.loadAssociationTypes(),
                    AttributeManager.getIdentifierAttribute()
                ).done(_.bind(function (associationTypes, identifierAttribute) {
                    this.setAssociationCount(associationTypes);
                    this.state.set('currentAssociationType', associationTypes.length ? _.first(associationTypes).code : null);
                    this.state.set('associationTypes', associationTypes);
                    this.identifierAttribute = identifierAttribute;
                    this.$el.html(
                        this.template({
                            product:          this.getData(),
                            locale:           UserContext.getUserContext().get('catalogLocale'),
                            state:            this.state.toJSON(),
                            associationTypes: associationTypes
                        })
                    );

                    if (associationTypes.length) {
                        _.each(this.datagrids, _.bind(function (datagrid) {
                            this.renderGrid(datagrid.name, datagrid.getInitialParams(this.state.get('currentAssociationType')));
                        }, this));
                        this.setListenerSelectors();
                    }

                    this.delegateEvents();
                }, this));

                return this;
            },
            loadAssociationTypes: function () {
                return AssociationManager.getAssociationTypes();
            },
            setAssociationCount: function (associationTypes) {
                var associations = this.getData().associations;

                _.each(associationTypes, function (associationType) {
                    var association = associations[associationType.code];

                    associationType.productCount = association && association.products ? association.products.length : 0;
                    associationType.groupCount = association && association.groups ? association.groups.length : 0;
                });
            },
            changeAssociationType: function (event) {
                var associationType = event.currentTarget.dataset.associationType;

                this.state.set('currentAssociationType', associationType);

                $(event.currentTarget).addClass('active').siblings('.active').removeClass('active');

                this.$('.tab-pane[data-association-type="' + associationType + '"]')
                    .addClass('active').siblings('.active').removeClass('active');

                this.updateListenerSelectors();

                _.each(this.datagrids, function (datagrid) {
                    mediator
                        .trigger('datagrid:setParam:' + datagrid.name, datagrid.paramName, datagrid.getParamValue(associationType))
                        .trigger('datagrid:doRefresh:' + datagrid.name);
                });
            },
            changeAssociationTargets: function (event) {
                var associationTarget = event.currentTarget.dataset.associationTarget;
                this.state.set('associationTarget', associationTarget);

                _.each(this.datagrids, function (datagrid, gridType) {
                    $('#' + datagrid.name)[gridType === associationTarget ? 'removeClass': 'addClass']('hide');
                });

                $(event.currentTarget).addClass('hide').siblings('[data-association-target]').removeClass('hide');
            },
            renderGrid: function (alias, params) {
                var urlParams = params;
                urlParams.alias = alias;
                urlParams.params = _.clone(params);

                $.get(Routing.generate('pim_datagrid_load', urlParams)).done(function (resp) {
                    $('#grid-' + alias).data({ 'metadata': resp.metadata, 'data': JSON.parse(resp.data) });

                    require(resp.metadata.requireJSModules, function () {
                        datagridBuilder(_.toArray(arguments));
                    });
                });
            },
            setListenerSelectors: function () {
                var gridNames = _.pluck(this.datagrids, 'name');

                mediator.on('column_form_listener:initialized', _.bind(function onColumnListenerReady (gridName) {
                    gridNames = _.without(gridNames, gridName);
                    if (!gridNames.length) {
                        mediator.off('column_form_listener:initialized', onColumnListenerReady);

                        this.updateListenerSelectors();
                    }
                }, this));
            },
            updateListenerSelectors: function () {
                var associationType = this.state.get('currentAssociationType');
                _.each(this.datagrids, function (datagrid, gridType) {
                    var appendFieldId = ['#', associationType, '-', gridType, '-appendfield'].join('');
                    var removeFieldId = ['#', associationType, '-', gridType, '-removefield'].join('');
                    mediator.trigger(
                        'column_form_listener:set_selectors:' + datagrid.name,
                        { included: appendFieldId, excluded: removeFieldId }
                    );
                });
            },
            selectModel: function (model, datagrid) {
                var currentAssociations = this.getCurrentAssociations();
                currentAssociations.push(datagrid.getModelIdentifier(model, this.identifierAttribute));
            },
            unselectModel: function (model, datagrid) {
                var currentAssociations = this.getCurrentAssociations();
                var index = currentAssociations.indexOf(datagrid.getModelIdentifier(model, this.identifierAttribute));
                if (-1 !== index) {
                    currentAssociations.splice(index, 1);
                }
            },
            getCurrentAssociations: function () {
                var associationType = this.state.get('currentAssociationType');
                var associationTarget = this.state.get('associationTarget');
                var associations = this.getRoot().model.get('associations');
                if (_.isArray(associations)) {
                    associations = {};
                    this.getRoot().model.set('associations', associations, {silent: true});
                }
                associations[associationType] = associations[associationType] || {};
                associations[associationType][associationTarget] = associations[associationType][associationTarget] || [];

                return associations[associationType][associationTarget];
            }
        });
    }
);
