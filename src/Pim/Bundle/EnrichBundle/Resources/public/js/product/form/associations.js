'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/tab/associations',
        'pim/association-manager',
        'routing',
        'oro/mediator',
        'oro/datagrid-builder'
    ],
    function($, _, Backbone, BaseForm, formTemplate, AssociationManager, Routing, mediator, datagridBuilder) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tab-pane active',
            id: 'product-associations',
            events: {
                'click .nav-tabs li': 'changeAssociation',
                'click #association-buttons button': 'changeAssociationType'
            },
            initialize: function () {
                this.state = new Backbone.Model();

                this.state.set('selectedAssociations', []);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('associations', 'Associations');

                this.datagrids = {
                    product: {
                        name:          'association-product-grid',
                        appendField:   'appendProducts',
                        removeField:   'removeProducts',
                        paramName:     'associationType',
                        getParamValue: _.identity
                    },
                    group: {
                        name:          'association-group-grid',
                        appendField:   'appendGroups',
                        removeField:   'removeGroups',
                        paramName:     'associatedIds',
                        getParamValue: function (associationId) {
                            // We need to get the associated group ids here
                            return [];
                        }
                    }
                };

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return;
                }

                this.loadAssociationTypes().done(_.bind(function (associationTypes) {
                    this.state.set('currentAssociationType', _.first(associationTypes).code);
                    this.$el.html(
                        this.template({
                            product: this.getData(),
                            locale: this.getParent().extensions['attributes'].getLocale(),
                            state: this.state.toJSON(),
                            associationTypes: associationTypes
                        })
                    );

                    this.renderGrid(
                        'association-product-grid',
                        {
                            product: this.getRoot().model.get('meta').id,
                            associationType: _.first(associationTypes).id
                        }
                    );

                    this.renderGrid(
                        'association-group-grid',
                        {
                            associatedIds: [] // We need to get the associated group ids here
                        }
                    );

                    this.delegateEvents();
                }, this));

                return this;
            },
            loadAssociationTypes: function () {
                return AssociationManager.getAssociationTypes();
            },
            changeAssociation: function (event) {
                this.state.set('currentAssociationType', event.currentTarget.dataset.associationType);

                $(event.currentTarget).addClass('active').siblings('.active').removeClass('active');

                var id = event.currentTarget.dataset.associationTypeId;

                _.each(this.datagrids, function(datagrid) {
                        mediator
                            .trigger('datagrid:removeParam:' + datagrid.name, datagrid.paramName)
                            .trigger('datagrid:setParam:' + datagrid.name, datagrid.paramName, datagrid.getParamValue(id))
                            .trigger('datagrid:doRefresh:' + datagrid.name);
                });
            },
            changeAssociationType: function (event) {
                var targetAssociation = event.currentTarget.dataset.targetAssociation;

                _.each(this.datagrids, function(datagrid, gridType) {
                    $('#' + datagrid.name)[gridType === targetAssociation ? 'removeClass': 'addClass']('hide');
                });

                $(event.currentTarget).addClass('hide').siblings('[data-target-association]').removeClass('hide');
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
            }
        });
    }
);
