'use strict';
/**
 * Association tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'backbone',
  'pim/form',
  'pim/template/product/tab/associations',
  'pim/template/product/tab/association-panes',
  'pim/template/common/modal-centered',
  'pim/fetcher-registry',
  'pim/attribute-manager',
  'pim/user-context',
  'routing',
  'oro/mediator',
  'oro/datagrid-builder',
  'oro/pageable-collection',
  'pim/datagrid/state',
  'require-context',
  'pim/form-builder',
  'pim/security-context',
], function(
  $,
  _,
  __,
  Backbone,
  BaseForm,
  formTemplate,
  panesTemplate,
  modalTemplate,
  FetcherRegistry,
  AttributeManager,
  UserContext,
  Routing,
  mediator,
  datagridBuilder,
  PageableCollection,
  DatagridState,
  requireContext,
  FormBuilder,
  securityContext
) {
  let state = {};

  return BaseForm.extend({
    template: _.template(formTemplate),
    panesTemplate: _.template(panesTemplate),
    modalTemplate: _.template(modalTemplate),
    className: 'tab-pane active product-associations',
    events: {
      'click .associations-list li': 'changeAssociationType',
      'click .target-button': 'changeAssociationTargets',
      'click .add-associations': 'addAssociations',
    },
    datagrids: {},
    config: {},

    /**
     * {@inheritdoc}
     */
    initialize: function(meta) {
      this.config = meta.config;

      state = {
        associationTarget: 'products',
      };

      this.datagrids = {
        products: {
          name: this.config.datagridName,
          getInitialParams: function(associationType) {
            let params = {
              product: this.getFormData().meta.id,
            };
            params[this.datagrids.products.paramName] = this.datagrids.products.getParamValue(associationType);
            params.dataLocale = UserContext.get('catalogLocale');

            return params;
          }.bind(this),
          paramName: 'associationType',
          getParamValue: function(associationType) {
            return _.findWhere(state.associationTypes, {code: associationType}).meta.id;
          }.bind(this),
          getModelIdentifier: function(model) {
            return model.get('identifier');
          },
        },
        groups: {
          name: 'association-group-grid',
          getInitialParams: function(associationType) {
            let params = {};
            params[this.paramName] = this.getParamValue(associationType);
            params.dataLocale = UserContext.get('catalogLocale');

            return params;
          },
          paramName: 'associatedIds',
          getParamValue: function(associationType) {
            const associationsMeta = this.getFormData().meta.associations;

            return associationsMeta[associationType] ? associationsMeta[associationType].groupIds : [];
          }.bind(this),
          getModelIdentifier: function(model) {
            return model.get('code');
          },
        },
      };

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    configure: function() {
      this.trigger('tab:register', {
        code: undefined === this.config.tabCode ? this.code : this.config.tabCode,
        isVisible: this.isVisible.bind(this),
        label: __('pim_enrich.entity.product.module.associations.title'),
      });

      _.each(
        this.datagrids,
        function(datagrid) {
          mediator.clear('datagrid:selectModel:' + datagrid.name);
          mediator.on(
            'datagrid:selectModel:' + datagrid.name,
            function(model) {
              this.selectModel(model, datagrid);
            }.bind(this)
          );

          mediator.clear('datagrid:unselectModel:' + datagrid.name);
          mediator.on(
            'datagrid:unselectModel:' + datagrid.name,
            function(model) {
              this.unselectModel(model, datagrid);
            }.bind(this)
          );
        }.bind(this)
      );

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.postUpdate.bind(this));

      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:locale_switcher:change',
        function(localeEvent) {
          if ('base_product' === localeEvent.context) {
            this.render();
          }
        }.bind(this)
      );

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function() {
      const code = undefined === this.config.tabCode ? this.code : this.config.tabCode;

      if (!this.configured || code !== this.getParent().getCurrentTab()) {
        return;
      }

      this.loadAssociationTypes().then(
        function(associationTypes) {
          const currentAssociationType = associationTypes.length ? _.first(associationTypes).code : null;

          if (
            null === this.getCurrentAssociationType() ||
            _.isUndefined(_.findWhere(associationTypes, {code: this.getCurrentAssociationType()}))
          ) {
            this.setCurrentAssociationType(currentAssociationType);
          }

          state.currentAssociationType = currentAssociationType;
          state.associationTypes = associationTypes;

          this.$el.html(
            this.template({
              product: this.getFormData(),
              locale: UserContext.get('catalogLocale'),
              associationTypes: associationTypes,
              currentAssociationTarget: this.getCurrentAssociationTarget(),
              currentAssociationTypeCode: this.getCurrentAssociationType(),
              currentAssociationType: _.findWhere(associationTypes, {code: this.getCurrentAssociationType()}),
              addAssociationsLabel: __('pim_enrich.entity.product.module.associations.add_associations'),
              addAssociationVisible: this.isAddAssociationsVisible(),
              datagridName: this.config.datagridName,
            })
          );
          this.renderPanes();

          if (associationTypes.length) {
            const currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
            this.renderGrid(currentGrid.name, currentGrid.getInitialParams(this.getCurrentAssociationType()));
            this.setListenerSelectors();
          }

          this.delegateEvents();
        }.bind(this)
      );

      return this;
    },

    /**
     * Prepend for each association type each tab content
     */
    renderPanes: function() {
      this.loadAssociationTypes().then(
        function(associationTypes) {
          this.setAssociationCount(associationTypes);
          this.$('.tab-content > .association-type').remove();
          this.$('.tab-content').prepend(
            this.panesTemplate({
              __: __,
              label: __('pim_enrich.entity.product.module.associations.association_type_selector'),
              locale: UserContext.get('catalogLocale'),
              associationTypes: associationTypes,
              currentAssociationType: this.getCurrentAssociationType(),
              currentAssociationTarget: this.getCurrentAssociationTarget(),
              numberAssociationLabelKey: 'pim_enrich.entity.product.module.associations.number_of_associations',
              targetLabel: __('pim_enrich.entity.product.module.associations.target'),
              showProductsLabel: __('pim_enrich.entity.product.module.associations.show_products'),
              showGroupsLabel: __('pim_enrich.entity.product.module.associations.show_groups'),
            })
          );
        }.bind(this)
      );
    },

    /**
     * Refresh the associations panel after model change
     */
    postUpdate: function() {
      if (this.isVisible()) {
        this.$('.selection-inputs input').val('');
        state.selectedAssociations = {};
        this.render();
      }
    },

    /**
     * @param {string} associationType
     */
    setCurrentAssociationType: function(associationType) {
      sessionStorage.setItem('current_association_type', associationType);
    },

    /**
     * @returns {string}
     */
    getCurrentAssociationType: function() {
      return sessionStorage.getItem('current_association_type');
    },

    /**
     * @param {string} associationTarget
     */
    setCurrentAssociationTarget: function(associationTarget) {
      sessionStorage.setItem('current_association_target', associationTarget);
    },

    /**
     * @returns {string}
     */
    getCurrentAssociationTarget: function() {
      return sessionStorage.getItem('current_association_target') || 'products';
    },

    /**
     * Fetch all the association types
     *
     * @returns {Promise}
     */
    loadAssociationTypes: function() {
      return FetcherRegistry.getFetcher('association-type')
        .fetchAll()
        .then(associationTypes => {
          const locale = UserContext.get('catalogLocale');

          return associationTypes.sort((associationType1, associationType2) => {
            const labelAssociationType1 = associationType1.labels[locale] || '[' + associationType1.code + ']';
            const labelAssociationType2 = associationType2.labels[locale] || '[' + associationType2.code + ']';

            return labelAssociationType1.localeCompare(labelAssociationType2, undefined, {sensitivity: 'base'});
          });
        });
    },

    /**
     * Compute associated items for a specified association type and put it in cache
     *
     * @param associationTypes
     */
    setAssociationCount: function(associationTypes) {
      const associations = this.getFormData().associations;

      _.each(associationTypes, function(assocType) {
        const association = associations[assocType.code];

        assocType.productCount = association && association.products ? association.products.length : 0;

        assocType.productModelCount = association && association.product_models ? association.product_models.length : 0;

        assocType.groupCount = association && association.groups ? association.groups.length : 0;
      });
    },

    /**
     * Switch the current association type
     *
     * @param {Event} event
     */
    changeAssociationType: function(event) {
      event.preventDefault();
      const associationType = event.currentTarget.dataset.associationType;
      this.setCurrentAssociationType(associationType);

      this.$(`.AknTitleContainer.association-type[data-association-type="${associationType}"]`)
        .removeClass('AknTitleContainer--hidden')
        .siblings('.AknTitleContainer.association-type:not(.AknTitleContainer--hidden)')
        .addClass('AknTitleContainer--hidden');

      this.renderPanes();
      this.updateListenerSelectors();

      const currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
      mediator
        .trigger(
          'datagrid:setParam:' + currentGrid.name,
          currentGrid.paramName,
          currentGrid.getParamValue(associationType)
        )
        .trigger('datagrid:doRefresh:' + currentGrid.name);
    },

    /**
     * Switch the current target (product or group)
     *
     * @param {Event} event
     */
    changeAssociationTargets: function(event) {
      const associationTarget = event.currentTarget.dataset.associationTarget;
      this.setCurrentAssociationTarget(associationTarget);

      _.each(
        this.datagrids,
        function(datagrid, gridType) {
          const method = gridType === associationTarget ? 'removeClass' : 'addClass';
          this.$('.' + datagrid.name)[method]('hide');
        }.bind(this)
      );

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

      const currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
      if (!this.isGridRendered(currentGrid)) {
        this.renderGrid(currentGrid.name, currentGrid.getInitialParams(this.getCurrentAssociationType()));
        this.setListenerSelectors();
      }
    },

    /**
     * Loads a complete grid from its grid name
     *
     * @param {string} gridName
     * @param {Object} params
     */
    renderGrid: function(gridName, params) {
      let urlParams = params;
      urlParams.alias = gridName;
      urlParams.params = _.clone(params);

      const datagridState = DatagridState.get(gridName, ['filters']);
      if (null !== datagridState.filters) {
        const collection = new PageableCollection();
        const filters = collection.decodeStateData(datagridState.filters);

        collection.processFiltersParams(urlParams, filters, gridName + '[_filter]');
      }

      $.get(Routing.generate('pim_datagrid_load', urlParams)).then(
        function(response) {
          let metadata = response.metadata;
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
          const queryParts = metadata.options.url.split('?');
          const url = queryParts[0];
          const queryString = decodeURIComponent(queryParts[1])
            .replace(/&?association-group-grid\[associatedIds\]\[\d+\]=\d+/g, '')
            .replace(/^&/, '');
          metadata.options.url = url + '?' + queryString;

          this.$('#grid-' + gridName).data({metadata: metadata, data: JSON.parse(response.data)});

          let gridModules = metadata.requireJSModules;
          gridModules.push('pim/datagrid/state-listener');
          gridModules.push('oro/datafilter-builder');
          gridModules.push('oro/datagrid/pagination-input');

          let resolvedModules = [];
          _.each(gridModules, function(module) {
            resolvedModules.push(requireContext(module));
          });

          datagridBuilder(resolvedModules);
        }.bind(this)
      );
    },

    /**
     * Sets the listeners to trigger the checkboxes of each grid
     */
    setListenerSelectors: function() {
      let gridNames = _.pluck(this.datagrids, 'name');

      mediator.on(
        'column_form_listener:initialized',
        function onColumnListenerReady(gridName) {
          gridNames = _.without(gridNames, gridName);
          if (!gridNames.length) {
            mediator.off('column_form_listener:initialized', onColumnListenerReady);

            this.updateListenerSelectors();
          }
        }.bind(this)
      );
    },

    /**
     * Updates the listeners to trigger the checkboxes of the current grid
     */
    updateListenerSelectors: function() {
      const associationType = this.getCurrentAssociationType();
      const selectedAssociations = state.selectedAssociations;

      _.each(this.datagrids, function(datagrid, gridType) {
        const appendFieldId = ['#', associationType, '-', gridType, '-appendfield'].join('');
        const removeFieldId = ['#', associationType, '-', gridType, '-removefield'].join('');

        if (
          selectedAssociations &&
          selectedAssociations[associationType] &&
          selectedAssociations[associationType][gridType]
        ) {
          $(appendFieldId).val(selectedAssociations[associationType][gridType].select.join(','));
          $(removeFieldId).val(selectedAssociations[associationType][gridType].unselect.join(','));
        }

        mediator.trigger('column_form_listener:set_selectors:' + datagrid.name, {
          included: appendFieldId,
          excluded: removeFieldId,
        });
      });
    },

    /**
     * Selects a line in the grid
     *
     * @param {Object} model    A grid model (i.e. a unique line)
     * @param {Object} datagrid
     */
    selectModel: function(model, datagrid) {
      const assocType = this.getCurrentAssociationType();
      const assocTarget = this.getDatagridTarget(datagrid);
      let currentAssociations = this.getCurrentAssociations(datagrid);

      currentAssociations.push(datagrid.getModelIdentifier(model));
      currentAssociations = _.uniq(currentAssociations);

      this.updateFormDataAssociations(currentAssociations, assocType, assocTarget);
      this.updateSelectedAssociations('select', datagrid, model.id);
    },

    /**
     * Unselect a line in the grid
     *
     * @param {Object} model    A grid model (i.e. a unique line)
     * @param {Object} datagrid
     */
    unselectModel: function(model, datagrid) {
      const assocType = this.getCurrentAssociationType();
      const assocTarget = this.getDatagridTarget(datagrid);

      let assocSubTarget = assocTarget;
      if (assocTarget === 'products') {
        // We check from what association target we have to remove model (products or product_models)
        assocSubTarget = model.attributes.document_type === 'product' ? 'products' : 'product_models';
      }

      const associationsField = this.getFormData().associations;
      let associations = associationsField[assocType][assocSubTarget];
      const index = associations.indexOf(datagrid.getModelIdentifier(model));
      if (-1 !== index) {
        associations.splice(index, 1);
      }

      this.updateFormDataAssociations(associations, assocType, assocSubTarget);
      this.updateSelectedAssociations('unselect', datagrid, model.id);
    },

    /**
     * Returns the current associations for a specified datagrid
     *
     * @param {Object} datagrid
     */
    getCurrentAssociations: function(datagrid) {
      const assocType = this.getCurrentAssociationType();
      const assocTarget = this.getDatagridTarget(datagrid);
      const associations = this.getFormData().associations;

      return associations[assocType][assocTarget];
    },

    /**
     * Update the user session selection to be able to restore it if he switches tabs
     *
     * @param {string} action
     * @param {Object} datagrid
     * @param {string|int} id
     */
    updateSelectedAssociations: function(action, datagrid, id) {
      const assocType = this.getCurrentAssociationType();
      const assocTarget = this.getDatagridTarget(datagrid);
      let selectedAssoc = state.selectedAssociations || {};
      selectedAssoc[assocType] = selectedAssoc[assocType] || {};
      if (!selectedAssoc[assocType][assocTarget]) {
        selectedAssoc[assocType][assocTarget] = {select: [], unselect: []};
      }

      const revertAction = 'select' === action ? 'unselect' : 'select';
      const index = selectedAssoc[assocType][assocTarget][revertAction].indexOf(id);

      if (-1 < index) {
        selectedAssoc[assocType][assocTarget][revertAction].splice(index, 1);
      } else {
        selectedAssoc[assocType][assocTarget][action].push(id);
        selectedAssoc[assocType][assocTarget][action] = _.uniq(selectedAssoc[assocType][assocTarget][action]);
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
    updateFormDataAssociations: function(currentAssociations, assocType, assocTarget) {
      let modelAssociations = this.getFormData().associations;
      modelAssociations[assocType][assocTarget] = currentAssociations;
      modelAssociations[assocType][assocTarget].sort();

      this.setData({associations: modelAssociations}, {silent: true});
    },

    /**
     * Return if the specified grid is already rendered
     *
     * @param {Object} grid
     *
     * @returns {boolean}
     */
    isGridRendered: function(grid) {
      return 0 < this.$('.grid-' + grid.name).find('[data-type="datagrid"][data-rendered="true"]').length;
    },

    /**
     * Get the given datagrid target (products or groups)
     *
     * @param {Object} datagrid
     *
     * @returns {string}
     */
    getDatagridTarget: function(datagrid) {
      let assocTarget = null;

      _.each(this.datagrids, function(grid, gridType) {
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
    isVisible: function() {
      return true;
    },

    isAddAssociationsVisible: function() {
      return securityContext.isGranted(this.config.aclAddAssociations);
    },

    /**
     * Opens the panel to select new products to associate
     */
    addAssociations: function() {
      this.launchProductPicker().then(productAndProductModelIdentifiers => {
        let productIds = [];
        let productModelIds = [];
        productAndProductModelIdentifiers.forEach(item => {
          const matchProductModel = item.match(/^product_model_(.*)$/);
          if (matchProductModel) {
            productModelIds.push(matchProductModel[1]);
          } else {
            const matchProduct = item.match(/^product_(.*)$/);
            productIds.push(matchProduct[1]);
          }
        });

        const assocType = this.getCurrentAssociationType();
        const previousProductIds = this.getFormData().associations[assocType].products;
        const previousProductModelIds = this.getFormData().associations[assocType].product_models;

        this.updateFormDataAssociations(previousProductIds.concat(productIds), assocType, 'products');

        this.updateFormDataAssociations(previousProductModelIds.concat(productModelIds), assocType, 'product_models');

        this.getRoot().trigger('pim_enrich:form:update-association');
      });
    },

    /**
     * Launch the association product picker
     *
     * @return {Promise}
     */
    launchProductPicker: function() {
      const deferred = $.Deferred();

      FormBuilder.build('pim-associations-product-picker-form').then(form => {
        FetcherRegistry.getFetcher('association-type')
          .fetch(this.getCurrentAssociationType())
          .then(associationType => {
            // TODO Delete setCustomTitle if possible
            //form.setCustomTitle();

            let modal = new Backbone.BootstrapModal({
              modalOptions: {
                backdrop: 'static',
                keyboard: false,
              },
              okCloses: false,
              title: __('pim_enrich.entity.product.module.associations.manage', {
                associationType: associationType.labels[UserContext.get('catalogLocale')],
              }),
              innerDescription: __('pim_enrich.entity.product.module.associations.manage_description'),
              content: '',
              okText: __('pim_common.confirm'),
              template: this.modalTemplate,
              innerClassName: 'AknFullPage--full',
            });

            modal.open();
            form.setElement(modal.$('.modal-body')).render();

            modal.on('cancel', deferred.reject);
            modal.on('ok', () => {
              const products = form.getItems().sort((a, b) => {
                return a.code < b.code;
              });
              modal.close();

              deferred.resolve(products);
            });
          });
      });

      return deferred.promise();
    },
  });
});
