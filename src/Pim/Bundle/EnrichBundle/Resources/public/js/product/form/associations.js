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
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/product/tab/associations',
        'pim/template/product/tab/association-panes',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/user-context',
        'routing',
        'oro/mediator',
        'oro/datagrid-builder',
        'oro/pageable-collection',
        'pim/datagrid/state',
        'require-context'
    ],
    function (
        $,
        _,
        __,
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
        DatagridState,
        requireContext
    ) {
        var state = {};

        return BaseForm.extend({
            template: _.template(formTemplate),
            panesTemplate: _.template(panesTemplate),
            className: 'tab-pane active product-associations',
            events: {
                'click .associations-list li': 'changeAssociationType',
                'click .target-button': 'changeAssociationTargets'
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
                            params[this.datagrids.products.paramName] =
                                this.datagrids.products.getParamValue(associationType);
                            params.dataLocale = UserContext.get('catalogLocale');

                            return params;
                        }.bind(this),
                        paramName: 'associationType',
                        getParamValue: function (associationType) {
                            return _.findWhere(state.associationTypes, {code: associationType}).meta.id;
                        }.bind(this),
                        getModelIdentifier: function (model) {
                            return model.get('identifier');
                        }
                    },
                    groups: {
                        name: 'association-group-grid',
                        getInitialParams: function (associationType) {
                            var params = {};
                            params[this.paramName] = this.getParamValue(associationType);
                            params.dataLocale = UserContext.get('catalogLocale');

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
                    label: __('pim_enrich.form.product.tab.associations.title')
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

                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        this.render();
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
                    return;
                }

                this.loadAssociationTypes().then(function (associationTypes) {
                    var currentAssociationType = associationTypes.length ? _.first(associationTypes).code : null;

                    if (null === this.getCurrentAssociationType()) {
                        this.setCurrentAssociationType(currentAssociationType);
                    }

                    state.currentAssociationType = currentAssociationType;
                    state.associationTypes       = associationTypes;

                    this.$el.html(
                        this.template({
                            product: this.getFormData(),
                            locale: UserContext.get('catalogLocale'),
                            associationTypes: associationTypes,
                            currentAssociationTarget: this.getCurrentAssociationTarget(),
                            currentAssociationTypeCode: this.getCurrentAssociationType(),
                            currentAssociationType: _.findWhere(
                                associationTypes,
                                {code: this.getCurrentAssociationType()}
                            )
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
                            __: __,
                            label: __('pim_enrich.form.product.tab.associations.association_type_selector'),
                            locale: UserContext.get('catalogLocale'),
                            associationTypes: associationTypes,
                            currentAssociationType: this.getCurrentAssociationType(),
                            currentAssociationTarget: this.getCurrentAssociationTarget(),
                            numberAssociationLabelKey:
                                'pim_enrich.form.product.tab.associations.info.number_of_associations',
                            targetLabel: __('pim_enrich.form.product.tab.associations.target'),
                            showProductsLabel: __('pim_enrich.form.product.tab.associations.info.show_products'),
                            showGroupsLabel: __('pim_enrich.form.product.tab.associations.info.show_groups')
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
                var associationType = event.currentTarget.dataset.associationtype;
                this.setCurrentAssociationType(associationType);

                this.$('.AknTitleContainer.association-type[data-association-type="' + associationType + '"]')
                    .removeClass('AknTitleContainer--hidden')
                    .siblings('.AknTitleContainer.association-type:not(.AknTitleContainer--hidden)')
                    .addClass('AknTitleContainer--hidden');

                this.renderPanes();
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
                const associationTarget = event.currentTarget.dataset.associationTarget;
                this.setCurrentAssociationTarget(associationTarget);

                _.each(this.datagrids, function (datagrid, gridType) {
                    var method = gridType === associationTarget ? 'removeClass' : 'addClass';
                    this.$('.' + datagrid.name)[method]('hide');
                }.bind(this));

                const text = event.currentTarget.textContent;
                $(event.currentTarget)
                    .addClass('AknDropdown-menuLink--active')
                    .siblings('.target-button')
                    .removeClass('AknDropdown-menuLink--active')
                    .end()
                    .closest('.AknDropdown')
                    .find('.AknActionButton-highlight')
                    .text(text);

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

                $.get(Routing.generate('pim_datagrid_load', urlParams)).then(function (response) {
                    var metadata = response.metadata;
                    /* Next lines are related to PIM-6113 and need some comments.
                     *
                     * When you just saved a datagrid from the Product Edit Form, you will have an URL like
                     * '/association-group-grid?...&associatedIds[]=1&associatedIds[]=2', in reference of the last
                     * checked groups in the datagrid.
                     *
                     * The fact is that there is 2 places where these parameters are set: in the URL, and in the
                     * datagrid state (state.parameters.associatedIds).
                     *
                     * If you do not drop the params of the URL (containing associatedIds array), you will have
                     * a mix of 2 times the same variable, defined at 2 different places. This leads to a refreshed
                     * datagrid with wrong checkboxes.
                     *
                     * To prevent this behavior, we removed the parameters passed in the URL before rendering the
                     * grid, to only allow datagrid state parameters.
                     */
                    var queryParts = metadata.options.url.split('?');
                    var url = queryParts[0];
                    var queryString = decodeURIComponent(queryParts[1])
                        .replace(/&?association-group-grid\[associatedIds\]\[\d+\]=\d+/g, '')
                        .replace(/^&/, '');
                    metadata.options.url = url + '?' + queryString;

                    this.$('#grid-' + gridName).data({ metadata: metadata, data: JSON.parse(response.data) });

                    var gridModules = metadata.requireJSModules;
                    gridModules.push('pim/datagrid/state-listener');

                    var resolvedModules = []
                    _.each(gridModules, function(module) {
                        resolvedModules.push(requireContext(module));
                    })
                    datagridBuilder(resolvedModules)
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

                currentAssociations.push(datagrid.getModelIdentifier(model));
                currentAssociations = _.uniq(currentAssociations);

                this.updateFormDataAssociations(currentAssociations, assocType, assocTarget);
                this.updateSelectedAssociations('select', datagrid, model.id);
            },
            unselectModel: function (model, datagrid) {
                var assocType           = this.getCurrentAssociationType();
                var assocTarget         = this.getDatagridTarget(datagrid);
                var currentAssociations = _.uniq(this.getCurrentAssociations(datagrid));

                var index = currentAssociations.indexOf(datagrid.getModelIdentifier(model));
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
