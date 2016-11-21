'use strict';
/**
 * Association tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/tab/associations',
        'text!pim/template/product/tab/association-panes',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/user-context',
        'routing',
        'oro/mediator',
        'oro/datagrid-builder',
        'oro/pageable-collection',
        'pim/datagrid/state'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        formTemplate,
        panesTemplate,
        FetcherRegistry,
        AttributeManager,
        UserContext,
        Routing,
        mediator,
        datagridBuilder,
        PageableCollection,
        DatagridState
    ) {
        var state = {};

        return BaseForm.extend({
            template: _.template(formTemplate),
            panesTemplate: _.template(panesTemplate),
            className: 'tab-pane active product-associations',
            events: {
                'click .associations-list li': 'changeAssociationType',
                'click .AknTitleContainer .target-button': 'changeAssociationTargets'
            },
            initialize: function () {
                state = {
                    associationTarget: 'products'
                };

                this.datagrids = {
                    products: {
                        name: 'association-product-grid',
                        getInitialParams: function (associationType) {
                            var params = {
                                product: this.getFormData().meta.id
                            };
                            var paramValue = this.datagrids.products.getParamValue(associationType);
                            params[this.datagrids.products.paramName] = paramValue;
                            params.dataLocale = UserContext.get('catalogLocale');

                            return params;
                        }.bind(this),
                        paramName: 'associationType',
                        getParamValue: function (associationType) {
                            return _.findWhere(state.associationTypes, {code: associationType}).id;
                        }.bind(this),
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
                        getParamValue: function (associationType) {
                            var associations = this.getFormData().meta.associations;
                            return associations[associationType] ? associations[associationType].groupIds : [];
                        }.bind(this),
                        getModelIdentifier: function (model) {
                            return model.get('code');
                        }
                    }
                };

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: this.isVisible.bind(this),
                    label: _.__('pim_enrich.form.product.tab.associations.title')
                });

                _.each(this.datagrids, function (datagrid) {
                    mediator.clear('datagrid:selectModel:' + datagrid.name);
                    mediator.on('datagrid:selectModel:' + datagrid.name, function (model) {
                        this.selectModel(model, datagrid);
                    }.bind(this));

                    mediator.clear('datagrid:unselectModel:' + datagrid.name);
                    mediator.on('datagrid:unselectModel:' + datagrid.name, function (model) {
                        this.unselectModel(model, datagrid);
                    }.bind(this));
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.postUpdate.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
                    return;
                }

                $.when(
                    this.loadAssociationTypes(),
                    FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                ).then(function (associationTypes, identifierAttribute) {
                    var currentAssociationType = associationTypes.length ? _.first(associationTypes).code : null;

                    if (null === this.getCurrentAssociationType()) {
                        this.setCurrentAssociationType(currentAssociationType);
                    }

                    state.currentAssociationType = currentAssociationType;
                    state.associationTypes       = associationTypes;
                    this.identifierAttribute     = identifierAttribute;

                    this.$el.html(
                        this.template({
                            product: this.getFormData(),
                            locale: UserContext.get('catalogLocale'),
                            associationTypes: associationTypes,
                            currentAssociationTarget: this.getCurrentAssociationTarget(),
                            currentAssociationType: this.getCurrentAssociationType()
                        })
                    );
                    this.renderPanes();

                    if (associationTypes.length) {
                        var currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
                        this.renderGrid(
                            currentGrid.name,
                            currentGrid.getInitialParams(this.getCurrentAssociationType())
                        );
                        this.setListenerSelectors();
                    }

                    this.delegateEvents();
                }.bind(this));

                return this;
            },
            renderPanes: function () {
                this.loadAssociationTypes().then(function (associationTypes) {
                    this.setAssociationCount(associationTypes);
                    this.$('.tab-content > .association-type').remove();
                    this.$('.tab-content').prepend(
                        this.panesTemplate({
                            locale: UserContext.get('catalogLocale'),
                            associationTypes: associationTypes,
                            currentAssociationType: this.getCurrentAssociationType(),
                            currentAssociationTarget: this.getCurrentAssociationTarget()
                        })
                    );
                }.bind(this));
            },
            postUpdate: function () {
                if (this.isVisible()) {
                    this.$('.selection-inputs input').val('');
                    state.selectedAssociations = {};
                    this.render();
                }
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
            getCurrentAssociationType: function () {
                return sessionStorage.getItem('current_association_type');
            },

            /**
             * @param {string} associationTarget
             */
            setCurrentAssociationTarget: function (associationTarget) {
                sessionStorage.setItem('current_association_target', associationTarget);
            },

            /**
             * @returns {string}
             */
            getCurrentAssociationTarget: function () {
                return sessionStorage.getItem('current_association_target') || 'products';
            },

            loadAssociationTypes: function () {
                return FetcherRegistry.getFetcher('association-type').fetchAll();
            },
            setAssociationCount: function (associationTypes) {
                var associations = this.getFormData().associations;

                _.each(associationTypes, function (assocType) {
                    var association = associations[assocType.code];

                    assocType.productCount = association && association.products ? association.products.length : 0;
                    assocType.groupCount = association && association.groups ? association.groups.length : 0;
                });
            },
            changeAssociationType: function (event) {
                event.preventDefault();
                var associationType = event.currentTarget.dataset.associationType;
                this.setCurrentAssociationType(associationType);

                $(event.currentTarget)
                    .addClass('active')
                    .siblings('.active')
                    .removeClass('active');

                this.$('.AknTitleContainer.association-type[data-association-type="' + associationType + '"]')
                    .removeClass('AknTitleContainer--hidden')
                    .siblings('.AknTitleContainer.association-type:not(.AknTitleContainer--hidden)')
                    .addClass('AknTitleContainer--hidden');

                this.updateListenerSelectors();

                var currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
                mediator
                    .trigger(
                        'datagrid:setParam:' + currentGrid.name,
                        currentGrid.paramName,
                        currentGrid.getParamValue(associationType)
                    )
                    .trigger('datagrid:doRefresh:' + currentGrid.name);
            },
            changeAssociationTargets: function (event) {
                var associationTarget = event.currentTarget.dataset.associationTarget;
                this.setCurrentAssociationTarget(associationTarget);

                _.each(this.datagrids, function (datagrid, gridType) {
                    var method = gridType === associationTarget ? 'removeClass' : 'addClass';
                    this.$('.' + datagrid.name)[method]('hide');
                }.bind(this));

                $(event.currentTarget)
                    .addClass('hide')
                    .siblings('.target-button')
                    .removeClass('hide');

                this.updateListenerSelectors();

                var currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
                if (!this.isGridRendered(currentGrid)) {
                    this.renderGrid(
                        currentGrid.name,
                        currentGrid.getInitialParams(this.getCurrentAssociationType())
                    );
                    this.setListenerSelectors();
                }
            },
            renderGrid: function (gridName, params) {
                var urlParams    = params;
                urlParams.alias  = gridName;
                urlParams.params = _.clone(params);

                var datagridState = DatagridState.get(gridName, ['filters']);
                if (null !== datagridState.filters) {
                    var collection = new PageableCollection();
                    var filters    = collection.decodeStateData(datagridState.filters);

                    collection.processFiltersParams(urlParams, filters, gridName + '[_filter]');
                }

                $.get(Routing.generate('pim_datagrid_load', urlParams)).then(function (resp) {
                    this.$('#grid-' + gridName).data({ 'metadata': resp.metadata, 'data': JSON.parse(resp.data) });

                    var gridModules = resp.metadata.requireJSModules;
                    gridModules.push('pim/datagrid/state-listener');
                    require(gridModules, function () {
                        datagridBuilder(_.toArray(arguments));
                    });
                }.bind(this));
            },
            setListenerSelectors: function () {
                var gridNames = _.pluck(this.datagrids, 'name');

                mediator.on('column_form_listener:initialized', function onColumnListenerReady(gridName) {
                    gridNames = _.without(gridNames, gridName);
                    if (!gridNames.length) {
                        mediator.off('column_form_listener:initialized', onColumnListenerReady);

                        this.updateListenerSelectors();
                    }
                }.bind(this));
            },
            updateListenerSelectors: function () {
                var associationType      = this.getCurrentAssociationType();
                var selectedAssociations = state.selectedAssociations;

                _.each(this.datagrids, function (datagrid, gridType) {
                    var appendFieldId = ['#', associationType, '-', gridType, '-appendfield'].join('');
                    var removeFieldId = ['#', associationType, '-', gridType, '-removefield'].join('');

                    if (selectedAssociations &&
                        selectedAssociations[associationType] &&
                        selectedAssociations[associationType][gridType]
                    ) {
                        $(appendFieldId).val(selectedAssociations[associationType][gridType].select.join(','));
                        $(removeFieldId).val(selectedAssociations[associationType][gridType].unselect.join(','));
                    }

                    mediator.trigger(
                        'column_form_listener:set_selectors:' + datagrid.name,
                        { included: appendFieldId, excluded: removeFieldId }
                    );
                });
            },
            selectModel: function (model, datagrid) {
                var assocType           = this.getCurrentAssociationType();
                var assocTarget         = this.getDatagridTarget(datagrid);
                var currentAssociations = this.getCurrentAssociations(datagrid);

                currentAssociations.push(datagrid.getModelIdentifier(model, this.identifierAttribute));
                currentAssociations = _.uniq(currentAssociations);

                this.updateFormDataAssociations(currentAssociations, assocType, assocTarget);
                this.updateSelectedAssociations('select', datagrid, model.id);
            },
            unselectModel: function (model, datagrid) {
                var assocType           = this.getCurrentAssociationType();
                var assocTarget         = this.getDatagridTarget(datagrid);
                var currentAssociations = _.uniq(this.getCurrentAssociations(datagrid));

                var index = currentAssociations.indexOf(datagrid.getModelIdentifier(model, this.identifierAttribute));
                if (-1 !== index) {
                    currentAssociations.splice(index, 1);
                }

                this.updateFormDataAssociations(currentAssociations, assocType, assocTarget);
                this.updateSelectedAssociations('unselect', datagrid, model.id);
            },
            getCurrentAssociations: function (datagrid) {
                var assocType = this.getCurrentAssociationType();
                var assocTarget = this.getDatagridTarget(datagrid);
                var associations = this.getFormData().associations;

                return associations[assocType][assocTarget];
            },

            /**
             * Update the user session selection to be able to restore it if he switches tabs
             *
             * @param {string} action
             * @param {Object} datagrid
             * @param {string|int} id
             */
            updateSelectedAssociations: function (action, datagrid, id) {
                var assocType     = this.getCurrentAssociationType();
                var assocTarget   = this.getDatagridTarget(datagrid);
                var selectedAssoc = state.selectedAssociations || {};
                selectedAssoc[assocType] = selectedAssoc[assocType] || {};
                if (!selectedAssoc[assocType][assocTarget]) {
                    selectedAssoc[assocType][assocTarget] = {'select': [], 'unselect': []};
                }

                var revertAction = 'select' === action ? 'unselect' : 'select';
                var index = selectedAssoc[assocType][assocTarget][revertAction].indexOf(id);

                if (-1 < index) {
                    selectedAssoc[assocType][assocTarget][revertAction].splice(index, 1);
                } else {
                    selectedAssoc[assocType][assocTarget][action].push(id);
                    selectedAssoc[assocType][assocTarget][action] = _.uniq(
                        selectedAssoc[assocType][assocTarget][action]
                    );
                }

                state.selectedAssociations = selectedAssoc;

                this.getRoot().trigger('pim_enrich:form:entity:update_state');
            },

            /**
             * Update the form data (product) associations
             *
             * @param {Array} currentAssociations
             * @param {string} assocType
             * @param {string} assocTarget
             */
            updateFormDataAssociations: function (currentAssociations, assocType, assocTarget) {
                var modelAssociations = this.getFormData().associations;
                modelAssociations[assocType][assocTarget] = currentAssociations;
                modelAssociations[assocType][assocTarget].sort();

                this.setData({'associations': modelAssociations}, {silent: true});
            },

            /**
             * Return if the specified grid is already rendered
             *
             * @param {Object} grid
             *
             * @returns {boolean}
             */
            isGridRendered: function (grid) {
                return 0 < this.$('.grid-' + grid.name)
                    .find('[data-type="datagrid"][data-rendered="true"]')
                    .length;
            },

            /**
             * Get the given datagrid target (products or groups)
             *
             * @param {Object} datagrid
             *
             * @returns {string}
             */
            getDatagridTarget: function (datagrid) {
                var assocTarget = null;

                _.each(this.datagrids, function (grid, gridType) {
                    if (grid.name === datagrid.name) {
                        assocTarget = gridType;
                    }
                });

                return assocTarget;
            },

            /**
             * Check if this extension is visible
             *
             * @returns {boolean}
             */
            isVisible: function () {
                return true;
            }
        });
    }
);
