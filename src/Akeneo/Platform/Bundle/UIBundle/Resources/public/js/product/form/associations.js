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
  'pim/user-context',
  'routing',
  'oro/mediator',
  'oro/datagrid-builder',
  'oro/pageable-collection',
  'pim/datagrid/state',
  'require-context',
  'pim/form-builder',
  'pim/security-context',
  'pim/i18n',
  'pimui/js/product/form/quantified-associations/components/QuantifiedAssociations',
  '@akeneo-pim-community/shared',
], function (
  $,
  _,
  __,
  Backbone,
  BaseForm,
  formTemplate,
  panesTemplate,
  modalTemplate,
  FetcherRegistry,
  UserContext,
  Routing,
  mediator,
  datagridBuilder,
  PageableCollection,
  DatagridState,
  requireContext,
  FormBuilder,
  securityContext,
  {getLabel},
  {QuantifiedAssociations},
  {filterErrors}
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
    associationCount: 0,

    /**
     * {@inheritdoc}
     */
    initialize: function (meta) {
      this.config = meta.config;
      this.validationErrors = [];

      state = {
        associationTarget: 'products',
      };

      this.datagrids = {
        products: {
          name: this.config.datagridName,
          getInitialParams: function (associationType) {
            let params = {
              product: this.getFormData().meta.id,
            };
            params[this.datagrids.products.paramName] = this.datagrids.products.getParamValue(associationType);
            params.dataLocale = UserContext.get('catalogLocale');
            params.dataScope = UserContext.get('catalogScope');

            return params;
          }.bind(this),
          paramName: 'associationType',
          getParamValue: function (associationType) {
            return _.findWhere(state.associationTypes, {code: associationType}).meta.id;
          }.bind(this),
          getModelIdentifier: function (model) {
            return model.get('document_type') === 'product_model'
              ? model.get('identifier')
              : model.get('id').replace('product-', '');
          },
        },
        groups: {
          name: 'association-group-grid',
          getInitialParams: function (associationType) {
            let params = {};
            params[this.paramName] = this.getParamValue(associationType);
            params.dataLocale = UserContext.get('catalogLocale');

            return params;
          },
          paramName: 'associatedIds',
          getParamValue: function (associationType) {
            const associationsMeta = this.getFormData().meta.associations;

            const associations = associationsMeta[associationType] ? associationsMeta[associationType].groupIds : [];
            if (associations.length === 0) {
              return ['emptyAssociations'];
            }

            return associations;
          }.bind(this),
          getModelIdentifier: function (model) {
            return model.get('code');
          },
        },
      };

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    configure: function () {
      this.trigger('tab:register', {
        code: undefined === this.config.tabCode ? this.code : this.config.tabCode,
        isVisible: this.isVisible.bind(this),
        label: __('pim_enrich.entity.product.module.associations.title', {count: this.associationCount}),
      });

      _.each(
        this.datagrids,
        function (datagrid) {
          mediator.clear('datagrid:selectModel:' + datagrid.name);
          mediator.on(
            'datagrid:selectModel:' + datagrid.name,
            function (model) {
              this.selectModel(model, datagrid);
            }.bind(this)
          );

          mediator.clear('datagrid:unselectModel:' + datagrid.name);
          mediator.on(
            'datagrid:unselectModel:' + datagrid.name,
            function (model) {
              this.unselectModel(model, datagrid);
            }.bind(this)
          );
        }.bind(this)
      );

      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.postUpdate.bind(this));
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () =>
        this.setValidationErrors({response: {quantified_associations: []}})
      );
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.setValidationErrors.bind(this));

      this.listenTo(
        this.getRoot(),
        'pim_enrich:form:locale_switcher:change pim_enrich:form:scope_switcher:change',
        function (localeEvent) {
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
    render: function () {
      this.updateAssociationCountInSidebar();

      const code = undefined === this.config.tabCode ? this.code : this.config.tabCode;

      if (!this.configured || code !== this.getParent().getCurrentTab()) {
        return;
      }

      this.loadAssociationTypes().then(
        function (associationTypes) {
          const currentAssociationType = associationTypes.length ? _.first(associationTypes).code : null;

          if (
            null === this.getCurrentAssociationType() ||
            _.isUndefined(_.findWhere(associationTypes, {code: this.getCurrentAssociationType()}))
          ) {
            this.setCurrentAssociationType(currentAssociationType);
          }
          const isQuantifiedAssociation = this.isQuantifiedAssociation(
            associationTypes,
            this.getCurrentAssociationType()
          );

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
              isQuantifiedAssociation,
            })
          );
          this.renderPanes();

          if (0 !== associationTypes.length && !isQuantifiedAssociation) {
            const currentGrid = this.datagrids[this.getCurrentAssociationTarget()];
            this.renderGrid(currentGrid.name, currentGrid.getInitialParams(this.getCurrentAssociationType()));
            this.setListenerSelectors();
          }

          this.renderQuantifiedAssociations();

          this.delegateEvents();
        }.bind(this)
      );

      return this;
    },

    updateAssociationCountInSidebar: function () {
      const newAssociationCount = this.getAssociationCount();
      if (this.associationCount !== newAssociationCount) {
        this.associationCount = newAssociationCount;

        this.trigger('tab:register', {
          code: undefined === this.config.tabCode ? this.code : this.config.tabCode,
          isVisible: this.isVisible.bind(this),
          label: __('pim_enrich.entity.product.module.associations.title', {count: newAssociationCount}),
        });
      }
    },

    setValidationErrors: function ({response}) {
      this.validationErrors = response;
      this.unmountQuantifiedAssociations();
      this.renderQuantifiedAssociations();
    },

    unmountQuantifiedAssociations: function () {
      const quantifiedAssociationsNode = document.getElementById('product-quantified-associations');
      if (quantifiedAssociationsNode) this.unmountReact();
    },

    /**
     * Prepend for each association type each tab content
     */
    renderPanes: function () {
      this.loadAssociationTypes().then(
        function (associationTypes) {
          const isQuantifiedAssociation = this.isQuantifiedAssociation(
            associationTypes,
            this.getCurrentAssociationType()
          );

          this.setAssociationCount(associationTypes);
          this.$('.tab-content > .association-type').remove();
          this.$('.tab-content').prepend(
            this.panesTemplate({
              __,
              getLabel,
              label: __('pim_enrich.entity.product.module.associations.association_type_selector'),
              locale: UserContext.get('catalogLocale'),
              associationTypes,
              currentAssociationType: this.getCurrentAssociationType(),
              currentAssociationTarget: this.getCurrentAssociationTarget(),
              numberAssociationLabelKey: isQuantifiedAssociation
                ? 'pim_enrich.entity.product.module.associations.number_of_quantified_associations'
                : 'pim_enrich.entity.product.module.associations.number_of_associations',
              targetLabel: __('pim_enrich.entity.product.module.associations.target'),
              showProductsLabel: __('pim_enrich.entity.product.module.associations.show_products'),
              showGroupsLabel: __('pim_enrich.entity.product.module.associations.show_groups'),
              isQuantifiedAssociation,
            })
          );
        }.bind(this)
      );
    },

    renderQuantifiedAssociations: function () {
      const associationTypeCode = this.getCurrentAssociationType();
      if (!this.isQuantifiedAssociation(state.associationTypes, associationTypeCode)) return;
      if (this.$('#product-quantified-associations').children().length !== 0) return;

      const quantifiedAssociations = this.getFormData().quantified_associations[associationTypeCode] || {
        products: [],
        product_models: [],
      };
      const parentQuantifiedAssociations = this.getFormData().meta.parent_quantified_associations[
        associationTypeCode
      ] || {
        products: [],
        product_models: [],
      };
      const errors = filterErrors(
        this.validationErrors.quantified_associations || [],
        `quantifiedAssociations.${associationTypeCode}`
      );

      const isUserOwner = this.getFormData().meta.is_owner ?? true;
      const props = {
        quantifiedAssociations,
        parentQuantifiedAssociations,
        errors,
        isCompact: false,
        isUserOwner,
        onAssociationsChange: updatedAssociations => {
          const formData = this.getFormData();
          formData.quantified_associations = {
            ...formData.quantified_associations,
            [associationTypeCode]: updatedAssociations,
          };

          this.setData(formData, {silent: true});
          this.getRoot().trigger('pim_enrich:form:entity:update_state');
        },
        onOpenPicker: () =>
          this.launchProductAndProductModelPicker().then(items =>
            items.map(item => {
              const quantifiedLink =
                'product_model' === item.document_type
                  ? {identifier: item.id, quantity: 1}
                  : {uuid: item.technical_id, quantity: 1};

              return {
                quantifiedLink: quantifiedLink,
                productType: item.document_type,
                errors: [],
                product: null,
              };
            })
          ),
      };
      this.renderReact(QuantifiedAssociations, props, this.$('#product-quantified-associations')[0]);
    },

    /**
     * Refresh the associations panel after model change
     */
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

    /**
     * Fetch all the association types
     *
     * @returns {Promise}
     */
    loadAssociationTypes: function () {
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
    setAssociationCount: function (associationTypes) {
      const {associations, quantified_associations} = this.getFormData();

      _.each(associationTypes, function (assocType) {
        const association = quantified_associations[assocType.code] || associations[assocType.code];

        assocType.productCount =
          association && association.products
            ? association.products.length
            : association && association.product_uuids
            ? association.product_uuids.length
            : 0;

        assocType.productModelCount = association && association.product_models ? association.product_models.length : 0;

        assocType.groupCount = association && association.groups ? association.groups.length : 0;
      });
    },

    /**
     * Switch the current association type
     *
     * @param {Event} event
     */
    changeAssociationType: function (event) {
      event.preventDefault();
      const associationType = event.currentTarget.dataset.associationType;
      this.setCurrentAssociationType(associationType);

      const isQuantifiedAssociation = this.isQuantifiedAssociation(state.associationTypes, associationType);
      if (isQuantifiedAssociation) {
        this.$('.association-grid-container').addClass('hide');
        this.$('#product-quantified-associations').removeClass('hide');
      } else {
        if (this.getCurrentAssociationTarget() === 'products') {
          this.$('.association-product-grid').removeClass('hide');
        } else {
          this.$('.association-group-grid').removeClass('hide');
        }
        this.$('#product-quantified-associations').addClass('hide');
      }

      this.$(`.AknTitleContainer.association-type[data-association-type="${associationType}"]`)
        .removeClass('AknTitleContainer--hidden')
        .siblings('.AknTitleContainer.association-type:not(.AknTitleContainer--hidden)')
        .addClass('AknTitleContainer--hidden');

      this.unmountQuantifiedAssociations();
      this.renderQuantifiedAssociations();
      this.renderPanes();
      this.updateListenerSelectors();

      const currentGrid = this.datagrids[this.getCurrentAssociationTarget()];

      if (!this.isGridRendered(currentGrid) && !isQuantifiedAssociation) {
        this.renderGrid(currentGrid.name, currentGrid.getInitialParams(this.getCurrentAssociationType()));
        this.setListenerSelectors();
      }

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
    changeAssociationTargets: function (event) {
      const associationTarget = event.currentTarget.dataset.associationTarget;
      this.setCurrentAssociationTarget(associationTarget);

      const isQuantifiedAssociation = this.isQuantifiedAssociation(
        state.associationTypes,
        this.getCurrentAssociationType()
      );

      _.each(
        this.datagrids,
        function (datagrid, gridType) {
          const method = gridType === associationTarget || isQuantifiedAssociation ? 'removeClass' : 'addClass';
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
      if (!this.isGridRendered(currentGrid) && !isQuantifiedAssociation) {
        this.renderGrid(currentGrid.name, currentGrid.getInitialParams(this.getCurrentAssociationType()));
        this.setListenerSelectors();
      }

      isQuantifiedAssociation
        ? this.$('#product-quantified-associations').removeClass('hide')
        : this.$('#product-quantified-associations').addClass('hide');
    },

    isQuantifiedAssociation: function (associationTypes, associationTypeCode) {
      if (!associationTypes || 0 === associationTypes.length) return false;

      const associationType = associationTypes.find(associationType => associationType.code === associationTypeCode);

      if (undefined === associationType) throw new Error(`Cannot find association type ${associationTypeCode}`);

      return associationType.is_quantified;
    },

    /**
     * Loads a complete grid from its grid name
     *
     * @param {string} gridName
     * @param {Object} params
     */
    renderGrid: function (gridName, params) {
      let urlParams = params;
      urlParams.alias = gridName;
      urlParams.params = _.clone(params);
      urlParams.filters = {scope: {value: params.dataScope}};

      const datagridState = DatagridState.get(gridName, ['filters']);
      if (null !== datagridState.filters) {
        const collection = new PageableCollection();
        const filters = collection.decodeStateData(datagridState.filters);

        collection.processFiltersParams(urlParams, filters, gridName + '[_filter]');
      }

      $.get(Routing.generate('pim_datagrid_load', urlParams)).then(
        function (response) {
          this.$('#grid-' + gridName).data({metadata: response.metadata, data: JSON.parse(response.data)});

          let gridModules = response.metadata.requireJSModules;
          gridModules.push('pim/datagrid/state-listener');
          gridModules.push('oro/datafilter-builder');
          gridModules.push('oro/datagrid/pagination-input');

          let resolvedModules = [];
          _.each(gridModules, function (module) {
            resolvedModules.push(requireContext(module));
          });

          datagridBuilder(resolvedModules);
        }.bind(this)
      );
    },

    /**
     * Sets the listeners to trigger the checkboxes of each grid
     */
    setListenerSelectors: function () {
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
    updateListenerSelectors: function () {
      const associationType = this.getCurrentAssociationType();
      const selectedAssociations = state.selectedAssociations;

      _.each(this.datagrids, function (datagrid, gridType) {
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
    selectModel: function (model, datagrid) {
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
    unselectModel: function (model, datagrid) {
      const assocType = this.getCurrentAssociationType();
      const assocTarget = this.getDatagridTarget(datagrid);

      let assocSubTarget = assocTarget;
      if (assocTarget === 'products') {
        // We check from what association target we have to remove model (products or product_models)
        assocSubTarget = model.attributes.document_type === 'product' ? 'product_uuids' : 'product_models';
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
    getCurrentAssociations: function (datagrid) {
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
    updateSelectedAssociations: function (action, datagrid, id) {
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
    updateFormDataAssociations: function (currentAssociations, assocType, assocTarget) {
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
    isGridRendered: function (grid) {
      return 0 < this.$('.grid-' + grid.name).find('[data-type="datagrid"][data-rendered="true"]').length;
    },

    /**
     * Get the given datagrid target (products or groups)
     *
     * @param {Object} datagrid
     *
     * @returns {string}
     */
    getDatagridTarget: function (datagrid) {
      let assocTarget = null;

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
    },

    isAddAssociationsVisible: function () {
      return securityContext.isGranted(this.config.aclAddAssociations);
    },

    /**
     * Opens the panel to select new products to associate
     */
    addAssociations: function () {
      this.launchProductPicker().then(items => {
        let productUuids = [];
        let productModelIds = [];

        items.forEach(item => {
          if ('product' === item.document_type) {
            productUuids.push(item.technical_id);
          } else if ('product_model' === item.document_type) {
            productModelIds.push(item.id);
          }
        });

        const assocType = this.getCurrentAssociationType();
        const previousProductUuids = this.getFormData().associations[assocType].product_uuids;
        const previousProductModelIds = this.getFormData().associations[assocType].product_models;

        this.updateFormDataAssociations(previousProductUuids.concat(productUuids), assocType, 'product_uuids');

        this.updateFormDataAssociations(previousProductModelIds.concat(productModelIds), assocType, 'product_models');

        this.getRoot().trigger('pim_enrich:form:update-association');
      });
    },

    /**
     * @TODO CPM-739: Do not use this function anymore
     * Launch the association product picker
     *
     * @return {Promise}
     */
    launchProductPicker: function () {
      const deferred = $.Deferred();

      FormBuilder.build('pim-associations-product-and-product-model-picker-modal').then(form => {
        FetcherRegistry.getFetcher('association-type')
          .fetch(this.getCurrentAssociationType())
          .then(associationType => {
            // TODO Delete setCustomTitle if possible
            //form.setCustomTitle();

            const formData = this.getFormData();
            const locale = UserContext.get('catalogLocale');
            const productLabel = getLabel(formData.meta.label, locale, formData.code || formData.identifier);

            let modal = new Backbone.BootstrapModal({
              modalOptions: {
                backdrop: 'static',
                keyboard: false,
              },
              okCloses: false,
              title: __('pim_enrich.entity.product.module.associations.manage', {
                associationType: getLabel(associationType.labels, locale, associationType.code),
              }),
              innerDescription: __('pim_enrich.entity.product.module.associations.manage_description', {productLabel}),
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
    launchProductAndProductModelPicker: function () {
      const deferred = $.Deferred();

      FormBuilder.build('pim-associations-product-and-product-model-picker-modal').then(form => {
        FetcherRegistry.getFetcher('association-type')
          .fetch(this.getCurrentAssociationType())
          .then(associationType => {
            const formData = this.getFormData();
            const locale = UserContext.get('catalogLocale');
            const productLabel = getLabel(formData.meta.label, locale, formData.code || formData.identifier);

            let modal = new Backbone.BootstrapModal({
              modalOptions: {
                backdrop: 'static',
                keyboard: false,
              },
              okCloses: false,
              title: __('pim_enrich.entity.product.module.associations.manage', {
                associationType: getLabel(associationType.labels, locale, associationType.code),
              }),
              innerDescription: __('pim_enrich.entity.product.module.associations.manage_description', {productLabel}),
              content: '',
              okText: __('pim_common.confirm'),
              template: this.modalTemplate,
              innerClassName: 'AknFullPage--full',
            });

            modal.open();
            form.setElement(modal.$('.modal-body')).render();

            modal.on('cancel', deferred.reject);
            modal.on('ok', () => {
              const productsAndProductModels = form.getItems().sort((a, b) => {
                return a.code < b.code;
              });
              modal.close();

              deferred.resolve(productsAndProductModels);
            });
          });
      });

      return deferred.promise();
    },
    getAssociationCount: function () {
      const {associations, quantified_associations} = this.getFormData();

      return [...Object.values(associations), ...Object.values(quantified_associations)].reduce(
        (typeCount, typeItem) => typeCount + Object.values(typeItem).reduce((count, item) => count + item.length, 0),
        0
      );
    },
  });
});
