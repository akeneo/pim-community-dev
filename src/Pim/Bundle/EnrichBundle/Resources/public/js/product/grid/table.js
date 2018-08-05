define(
    [
        'underscore',
        'jquery',
        'pim/router',
        'oro/datagrid-builder',
        'oro/pageable-collection',
        'pim/datagrid/state',
        'require-context',
        'pim/form',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/datagrid/state-listener',
        'oro/loading-mask'
    ],
    function(
        _,
        $,
        Routing,
        datagridBuilder,
        PageableCollection,
        DatagridState,
        requireContext,
        BaseForm,
        UserContext,
        FetcherRegistry,
        StateListener,
        LoadingMask
    ) {
        return BaseForm.extend({
            config: {},
            loadingMask: null,

            /**
             * @inheritdoc
             */
            initialize(options) {
                this.config = options.config;
                this.loadingMask = new LoadingMask();

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Returns the stored display type for the given grid
             *
             * @return {String}
             */
            getStoredDisplayType() {
                return localStorage.getItem(`display-selector:${this.config.gridName}`);
            },

            /**
             * Fetch default view for grid
             * @return {Promise}
             */
            getDefaultView() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultUserView(this.config.gridName)
                    .then(defaultUserView => defaultUserView.view);
            },

            /**
             * Fetch default columns for grid
             * @return {Promise}
             */
            getDefaultColumns() {
                return FetcherRegistry.getFetcher('datagrid-view')
                    .defaultColumns(this.config.gridName);
            },

            /**
             * Build the datagrid
             * @param  {Object} resp Datagrid load response
             */
            loadDataGrid(resp) {
                console.time('loadDatagrid')
                if (typeof resp === 'string' || null === resp) {
                    return;
                }

                const { gridName } = this.config;
                const dataLocale = UserContext.get('catalogLocale');
                const state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                // if (state.columns) {
                //     resp.metadata.state.parameters = _.extend({},
                //         resp.metadata.state.parameters,
                //         {
                //             view: {
                //                 columns: state.columns,
                //                 id: state.view
                //             }
                //         }
                //     );
                // }

                // resp.metadata = this.applyDisplayType(resp.metadata);

                $(`#grid-${gridName}`).data({
                    metadata: resp.metadata,
                    data: JSON.parse(resp.data)
                });

                const url = decodeURI(resp.metadata.options.url).split('?')[0];
                const localeParam = $.param({ dataLocale });
                resp.metadata.options.url =  `${url}?${localeParam}`;

                datagridBuilder([StateListener]);

                console.timeEnd('loadDatagrid')
                // this.loadingMask.hide();
            },

            /**
             * Gets the allowed display types from the datagrid config and applies them
             * The allowed options are:
             *
             * manageColumns: Display column selector button or not
             * rowView: The module to display a row
             * label: The name of the display type in the display-selector
             *
             * @param  {Object} gridMetadata
             * @param  {Object} selectedType
             * @return {Object}
             */
            applyDisplayType(gridMetadata) {
                const selectedType = this.getStoredDisplayType();
                const metadata = Object.assign({}, gridMetadata);
                const displayTypes = metadata.options.displayTypes || {};
                const displayType = displayTypes[selectedType];

                if (selectedType === 'default' || undefined === displayType) {
                    return gridMetadata;
                }

                metadata.options.manageColumns = displayType.manageColumns;
                metadata.options.rowView = displayType.rowView;

                $('#product-grid').addClass(`AknGrid--${selectedType}`);

                return metadata;
            },

            /**
             * Get the initial grid params with locale
             * @return {Object} urlParams
             */
            getInitialParams() {
                const dataLocale = UserContext.get('catalogLocale');
                const alias = this.config.gridName;
                const urlParams = { dataLocale, alias };
                urlParams.params = { dataLocale };

                return urlParams;
            },

            /**
             * Set the columns on the datagrid state
             * @param  {Array} columns   An array of columns
             * @param  {Object} urlParams Url params
             * @return {Object}
             */
            applyColumns(columns, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;
                if (_.isArray(columns)) columns = columns.join();

                urlParams[`${gridName}[_parameters][view][columns]`] = columns;
                DatagridState.set(gridName, { columns: columns });

                return urlParams;
            },

            /**
             * Set the selected view on the datagrid state
             * @param  {String} viewId    The id of the view
             * @param  {Object} urlParams Url params
             * @return {Object}
             */
            applyView(viewId, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;

                urlParams[`${gridName}[_parameters][view][id]`] = viewId;
                DatagridState.set(gridName, { view: viewId });

                return urlParams;
            },

            /**
             * Apply filters to the datagrid params
             * @param  {String} rawFilters Filters as string
             * @param  {Object} urlParams  Url params
             * @return {Object}
             */
            applyFilters(rawFilters, urlParams) {
                urlParams = _.clone(urlParams);
                const { gridName } = this.config;
                let filters = PageableCollection.prototype.decodeStateData(rawFilters);
                let options = {};

                if (!_.isEmpty(filters.filters)) {
                    options = {
                        state: {
                            filters: _.omit(filters.filters, 'scope')
                        }
                    };
                }

                let collection = new PageableCollection(null, options);
                collection.processFiltersParams(urlParams, filters, `${gridName}[_filter]`);

                for (let column in filters.sorters) {
                    urlParams[`${gridName}[_sort_by][${column}]`] =
                    1 === parseInt(filters.sorters[column]) ?
                    'DESC' :
                    'ASC';
                }

                if (filters.pageSize) {
                    urlParams[`${gridName}[_pager][_per_page]`] = 25;
                }

                if (filters.currentPage) {
                    urlParams[`${gridName}[_pager][_page]`] = filters.currentPage;
                }

                DatagridState.set(gridName, { filters: rawFilters });

                return urlParams;
            },

            /**
             * Apply filters columns and view for the datagrid
             * @param {Array} defaultColumns
             * @param {String} defaultView
             */
            setDatagridState(defaultColumns, defaultView) {
                console.time('setDatagridState')
                // const { gridName, datagridLoadUrl} = this.config;
                // let params = this.getInitialParams();

                // if (!DatagridState.get(gridName, ['view'])) {
                //     DatagridState.refreshFiltersFromUrl(gridName);
                // }

                // const state = DatagridState.get(gridName, ['view', 'filters', 'columns']);

                // if (defaultView && ('0' === state.view || null === state.view)) {
                //     params = this.applyView(defaultView.id, params);
                //     params = this.applyFilters(defaultView.filters, params);
                //     params = this.applyColumns(defaultView.columns, params);
                // } else {
                //     if (state.view) params = this.applyView(state.view, params);
                //     if (state.filters) params = this.applyFilters(state.filters, params);
                //     params = this.applyColumns(state.columns || defaultColumns, params);
                // }

                // this.getRoot().trigger('datagrid:getParams', params);

                const promise = new Promise((resolve, reject) => {
                    this.loadDataGrid({
  "metadata": {
    "requireJSModules": [],
    "options": {
      "gridName": "product-grid",
      "entityHint": "product",
      "filtersAsColumn": true,
      "displayTypes": {
        "default": {
          "label": "grid.display_selector.list"
        },
        "gallery": {
          "label": "grid.display_selector.gallery",
          "rowView": "oro/datagrid/product-row",
          "manageColumns": false
        }
      },
      "toolbarOptions": {
        "hide": false,
        "pageSize": {
          "hide": false,
          "default_per_page": 25,
          "items": [
            10,
            25,
            50,
            100
          ]
        },
        "pagination": {
          "hide": false
        }
      },
      "multipleSorting": false,
      "url": "/datagrid/product-grid?product-grid%5BdataLocale%5D=en_US&product-grid%5B_filter%5D%5Bscope%5D%5Bvalue%5D=ecommerce&product-grid%5B_sort_by%5D%5Bupdated%5D=DESC&product-grid%5B_pager%5D%5B_per_page%5D=25&product-grid%5B_pager%5D%5B_page%5D=1&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject%20Object%5D"
    },
    "rowActions": {
      "edit": {
        "type": "navigate-product-and-product-model",
        "label": null,
        "icon": null,
        "rowAction": true,
        "name": "edit",
        "launcherOptions": {
          "onClickReturnValue": false,
          "runAction": true,
          "className": "no-hash"
        }
      },
      "edit_attributes": {
        "launcherOptions": {
          "onClickReturnValue": false,
          "runAction": true,
          "className": "AknIconButton AknIconButton--small AknIconButton--edit"
        },
        "type": "navigate-product-and-product-model",
        "label": "Edit attributes of the product",
        "tabRedirects": {
          "product": "pim-product-edit-form-attributes",
          "product_model": "pim-product-model-edit-form-attributes"
        },
        "name": "edit_attributes"
      },
      "edit_categories": {
        "launcherOptions": {
          "onClickReturnValue": false,
          "runAction": true,
          "className": "AknIconButton AknIconButton--small AknIconButton--folder"
        },
        "type": "navigate-product-and-product-model",
        "label": "Classify the product",
        "tabRedirects": {
          "product": "pim-product-edit-form-categories",
          "product_model": "pim-product-edit-form-categories"
        },
        "name": "edit_categories"
      },
      "delete": {
        "launcherOptions": {
          "className": "AknIconButton AknIconButton--small AknIconButton--trash"
        },
        "type": "delete-product",
        "label": "Delete the product",
        "link": "delete_link",
        "name": "delete",
        "confirmation": true
      },
      "toggle_status": {
        "launcherOptions": {
          "className": "AknIconButton AknIconButton--small AknIconButton--switch"
        },
        "type": "toggle-product",
        "label": "Toggle status",
        "link": "toggle_status_link",
        "name": "toggle_status",
        "frontend_type": "ajax"
      }
    },
    "massActions": {
      "product_edit": {
        "type": "edit",
        "label": "pim.grid.mass_action.mass_edit",
        "handler": "product_mass_edit",
        "route": "pim_enrich_mass_edit_action",
        "className": "AknButton AknButton--action AknButtonList-item",
        "route_parameters": {
          "actionName": "product-edit"
        },
        "name": "product_edit",
        "frontend_type": "redirect"
      },
      "sequential_edit": {
        "type": "sequential_edit",
        "label": "pim.grid.mass_action.sequential_edit",
        "className": "AknButton AknButton--action AknButtonList-item",
        "name": "sequential_edit"
      },
      "delete_products_and_product_models": {
        "type": "mass_delete",
        "label": "pim.grid.mass_action.delete",
        "entity_name": "product",
        "handler": "product_mass_delete",
        "className": "AknButton AknButton--important AknButtonList-item",
        "messages": {
          "confirm_title": "pim_datagrid.mass_action.delete.confirm_title",
          "confirm_content": "pim_datagrid.mass_action.delete.confirm_content",
          "confirm_ok": "pim_datagrid.mass_action.delete.confirm_ok",
          "success": "pim_datagrid.mass_action.delete.success",
          "error": "pim_datagrid.mass_action.delete.error",
          "empty_selection": "pim_datagrid.mass_action.delete.empty_selection"
        },
        "name": "delete_products_and_product_models",
        "frontend_type": "ajax",
        "route": "oro_datagrid_mass_action",
        "route_parameters": [],
        "confirmation": true
      },
      "quick_export_grid_context_xlsx": {
        "type": "export",
        "label": "pim.grid.mass_action.quick_export.xlsx_grid_context",
        "handler": "product_quick_export",
        "route": "pim_datagrid_export_product_index",
        "route_parameters": {
          "_format": "xlsx",
          "_contentType": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          "_jobCode": "xlsx_product_grid_context_quick_export",
          "_displayedColumnsOnly": 1
        },
        "context": {
          "withHeader": true
        },
        "messages": {
          "empty_selection": "pim_datagrid.mass_action.delete.empty_selection"
        },
        "launcherOptions": {
          "group": "quick_export"
        },
        "name": "quick_export_grid_context_xlsx",
        "frontend_type": "export",
        "frontend_options": []
      },
      "quick_export_xlsx": {
        "type": "export",
        "label": "pim.grid.mass_action.quick_export.xlsx_all",
        "handler": "product_quick_export",
        "route": "pim_datagrid_export_product_index",
        "route_parameters": {
          "_format": "xlsx",
          "_contentType": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          "_jobCode": "xlsx_product_quick_export",
          "_displayedColumnsOnly": 0
        },
        "context": {
          "withHeader": true
        },
        "messages": {
          "empty_selection": "pim_datagrid.mass_action.delete.empty_selection"
        },
        "launcherOptions": {
          "group": "quick_export"
        },
        "name": "quick_export_xlsx",
        "frontend_type": "export",
        "frontend_options": []
      },
      "quick_export_grid_context_csv": {
        "type": "export",
        "label": "pim.grid.mass_action.quick_export.csv_grid_context",
        "handler": "product_quick_export",
        "route": "pim_datagrid_export_product_index",
        "route_parameters": {
          "_format": "csv",
          "_contentType": "text/csv",
          "_jobCode": "csv_product_grid_context_quick_export",
          "_displayedColumnsOnly": 1
        },
        "context": {
          "withHeader": true
        },
        "messages": {
          "empty_selection": "pim_datagrid.mass_action.delete.empty_selection"
        },
        "launcherOptions": {
          "group": "quick_export"
        },
        "name": "quick_export_grid_context_csv",
        "frontend_type": "export",
        "frontend_options": []
      },
      "quick_export_csv": {
        "type": "export",
        "label": "pim.grid.mass_action.quick_export.csv_all",
        "handler": "product_quick_export",
        "route": "pim_datagrid_export_product_index",
        "route_parameters": {
          "_format": "csv",
          "_contentType": "text/csv",
          "_jobCode": "csv_product_quick_export",
          "_displayedColumnsOnly": 0
        },
        "context": {
          "withHeader": true
        },
        "messages": {
          "empty_selection": "pim_datagrid.mass_action.delete.empty_selection"
        },
        "launcherOptions": {
          "group": "quick_export"
        },
        "name": "quick_export_csv",
        "frontend_type": "export",
        "frontend_options": []
      }
    },
    "massActionsGroups": {
      "bulk_actions": {
        "label": "pim_datagrid.mass_action_group.bulk_actions.label"
      },
      "quick_export": {
        "label": "pim_datagrid.mass_action_group.quick_export.label"
      }
    },
    "columns": [
      {
        "label": "ID",
        "type": "string",
        "editable": false,
        "renderable": true,
        "name": "identifier",
        "sortable": true
      },
      {
        "label": "Image",
        "type": "product-and-product-model-image",
        "editable": false,
        "renderable": true,
        "name": "image"
      },
      {
        "label": "Label",
        "type": "product-and-product-model-label",
        "editable": false,
        "renderable": true,
        "name": "label"
      },
      {
        "label": "Family",
        "type": "string",
        "editable": false,
        "renderable": true,
        "name": "family",
        "sortable": true
      },
      {
        "label": "Status",
        "type": "enabled",
        "editable": false,
        "renderable": true,
        "name": "enabled",
        "sortable": true
      },
      {
        "label": "Complete",
        "type": "completeness",
        "editable": false,
        "renderable": true,
        "name": "completeness",
        "sortable": true
      },
      {
        "label": "Created at",
        "type": "string",
        "editable": false,
        "renderable": true,
        "name": "created",
        "sortable": true
      },
      {
        "label": "Updated at",
        "type": "string",
        "editable": false,
        "renderable": true,
        "name": "updated",
        "sortable": true
      },
      {
        "label": "Variant products",
        "type": "complete-variant-product",
        "editable": false,
        "renderable": true,
        "name": "complete_variant_products"
      }
    ],
    "state": {
      "filters": {
        "scope": {
          "value": "ecommerce"
        }
      },
      "sorters": {
        "updated": "DESC"
      },
      "currentPage": "1",
      "pageSize": "25"
    },
    "filters": [
      {
        "name": "family",
        "label": "Family",
        "enabled": true,
        "choices": [],
        "type": "select2-rest-choice",
        "populateDefault": true,
        "choiceUrl": "pim_enrich_family_rest_index",
        "choiceUrlParams": null,
        "emptyChoice": true
      },
      {
        "name": "groups",
        "label": "Groups",
        "enabled": true,
        "choices": [],
        "type": "select2-choice",
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\Group",
          "dataLocale": "en_US",
          "collectionId": null,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": false
      },
      {
        "name": "enabled",
        "label": "Status",
        "choices": [
          {
            "label": "Enabled",
            "value": "1"
          },
          {
            "label": "Disabled",
            "value": "0"
          }
        ],
        "enabled": true,
        "type": "choice",
        "populateDefault": true
      },
      {
        "name": "scope",
        "label": "Channel",
        "choices": [
          {
            "label": "Ecommerce",
            "value": "ecommerce"
          },
          {
            "label": "Mobile",
            "value": "mobile"
          },
          {
            "label": "Print",
            "value": "print"
          }
        ],
        "enabled": true,
        "type": "product_scope",
        "populateDefault": false
      },
      {
        "name": "completeness",
        "label": "Complete",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "2"
          }
        ],
        "enabled": true,
        "type": "product_completeness",
        "populateDefault": true
      },
      {
        "name": "created",
        "label": "Created at",
        "choices": [
          {
            "label": "between",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "not between",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "more than",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "less than",
            "value": "4",
            "data": 4,
            "attr": []
          }
        ],
        "enabled": true,
        "type": "date",
        "typeValues": {
          "between": 1,
          "notBetween": 2,
          "moreThan": 3,
          "lessThan": 4
        },
        "externalWidgetOptions": {
          "firstDay": 0
        }
      },
      {
        "name": "updated",
        "label": "Updated at",
        "choices": [
          {
            "label": "between",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "not between",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "more than",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "less than",
            "value": "4",
            "data": 4,
            "attr": []
          }
        ],
        "enabled": true,
        "type": "date",
        "typeValues": {
          "between": 1,
          "notBetween": 2,
          "moreThan": 3,
          "lessThan": 4
        },
        "externalWidgetOptions": {
          "firstDay": 0
        }
      },
      {
        "name": "label_or_identifier",
        "label": "Label or identifier",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          }
        ],
        "enabled": true,
        "type": "label_or_identifier"
      },
      {
        "name": "parent",
        "label": "Parent",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "in list",
            "value": "in",
            "data": "in",
            "attr": []
          }
        ],
        "enabled": true,
        "type": "parent"
      },
      {
        "name": "name",
        "label": "Name",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 2,
        "group": "Marketing",
        "groupOrder": 1
      },
      {
        "name": "collection",
        "label": "Collection",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 2,
        "group": "Marketing",
        "groupOrder": 1,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 65,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "description",
        "label": "Description",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 3,
        "group": "Marketing",
        "groupOrder": 1
      },
      {
        "name": "response_time",
        "label": "Response time (ms)",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 4,
        "group": "Marketing",
        "groupOrder": 1,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "variation_name",
        "label": "Variant Name",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 4,
        "group": "Marketing",
        "groupOrder": 1
      },
      {
        "name": "release_date",
        "label": "Release date",
        "choices": [
          {
            "label": "between",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "not between",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "more than",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "less than",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "date",
        "order": 5,
        "group": "Marketing",
        "groupOrder": 1,
        "typeValues": {
          "between": 1,
          "notBetween": 2,
          "moreThan": 3,
          "lessThan": 4
        },
        "externalWidgetOptions": {
          "firstDay": 0
        }
      },
      {
        "name": "variation_description",
        "label": "Variant description",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 5,
        "group": "Marketing",
        "groupOrder": 1
      },
      {
        "name": "weight",
        "label": "Weight",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "metric",
        "order": 1,
        "group": "Technical",
        "groupOrder": 2,
        "family": "Weight",
        "formatterOptions": {
          "decimals": 2,
          "grouping": true,
          "orderSeparator": ",",
          "decimalSeparator": "."
        },
        "units": {
          "MILLIGRAM": "mg",
          "GRAM": "g",
          "KILOGRAM": "kg",
          "TON": "t",
          "GRAIN": "gr",
          "DENIER": "denier",
          "ONCE": "once",
          "MARC": "marc",
          "LIVRE": "livre",
          "OUNCE": "oz",
          "POUND": "lb"
        }
      },
      {
        "name": "maximum_scan_size",
        "label": "Maximum scan size",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "metric",
        "order": 2,
        "group": "Technical",
        "groupOrder": 2,
        "family": "Length",
        "formatterOptions": {
          "decimals": 2,
          "grouping": true,
          "orderSeparator": ",",
          "decimalSeparator": "."
        },
        "units": {
          "MILLIMETER": "mm",
          "CENTIMETER": "cm",
          "DECIMETER": "dm",
          "METER": "m",
          "DEKAMETER": "dam",
          "HECTOMETER": "hm",
          "KILOMETER": "km",
          "MIL": "mil",
          "INCH": "in",
          "FEET": "ft",
          "YARD": "yd",
          "CHAIN": "ch",
          "FURLONG": "fur",
          "MILE": "mi"
        }
      },
      {
        "name": "color_scanning",
        "label": "Color scanning",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 3,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "power_requirements",
        "label": "Power requirements",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 4,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "maximum_print_size",
        "label": "Maximum print size",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 5,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 11,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "sensor_type",
        "label": "Sensor type",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 6,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 12,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "total_megapixels",
        "label": "Total megapixels",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 7,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "optical_zoom",
        "label": "Optical zoom",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 8,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "image_stabilizer",
        "label": "Image stabilizer",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 9,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "camera_type",
        "label": "Camera type",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 10,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 16,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "thd",
        "label": "Total Harmonic Distortion (THD)",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 11,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "snr",
        "label": "Signal-to-Noise Ratio (SNR)",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 12,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "headphone_connectivity",
        "label": "Headphone connectivity",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 13,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 19,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "maximum_video_resolution",
        "label": "Maximum video resolution",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 14,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 20,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "maximum_frame_rate",
        "label": "Maximum frame rate",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 15,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "multifunctional_functions",
        "label": "All-in-one functions",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 16,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 22,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "display_srgb",
        "label": "Display sRGB",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 17,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "display_color",
        "label": "Display Color",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 18,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "display_diagonal",
        "label": "Display diagonal",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "metric",
        "order": 19,
        "group": "Technical",
        "groupOrder": 2,
        "family": "Length",
        "formatterOptions": {
          "decimals": 2,
          "grouping": true,
          "orderSeparator": ",",
          "decimalSeparator": "."
        },
        "units": {
          "MILLIMETER": "mm",
          "CENTIMETER": "cm",
          "DECIMETER": "dm",
          "METER": "m",
          "DEKAMETER": "dam",
          "HECTOMETER": "hm",
          "KILOMETER": "km",
          "MIL": "mil",
          "INCH": "in",
          "FEET": "ft",
          "YARD": "yd",
          "CHAIN": "ch",
          "FURLONG": "fur",
          "MILE": "mi"
        }
      },
      {
        "name": "viewing_area",
        "label": "Effective viewing area",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "metric",
        "order": 20,
        "group": "Technical",
        "groupOrder": 2,
        "family": "Length",
        "formatterOptions": {
          "decimals": 2,
          "grouping": true,
          "orderSeparator": ",",
          "decimalSeparator": "."
        },
        "units": {
          "MILLIMETER": "mm",
          "CENTIMETER": "cm",
          "DECIMETER": "dm",
          "METER": "m",
          "DEKAMETER": "dam",
          "HECTOMETER": "hm",
          "KILOMETER": "km",
          "MIL": "mil",
          "INCH": "in",
          "FEET": "ft",
          "YARD": "yd",
          "CHAIN": "ch",
          "FURLONG": "fur",
          "MILE": "mi"
        }
      },
      {
        "name": "camera_brand",
        "label": "Brand",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 21,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 27,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "camera_model_name",
        "label": "Model name",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 22,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "short_description",
        "label": "Short description",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 23,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "max_image_resolution",
        "label": "Maximum image resolution",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 24,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "image_resolutions",
        "label": "Still image resolution(s)",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 25,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "supported_aspect_ratios",
        "label": "Supported aspect ratios",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 26,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "supported_image_format",
        "label": "Supported image format",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 27,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "lens_mount_interface",
        "label": "Lens mount interface",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 28,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "focus",
        "label": "Focus",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 29,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "focus_adjustement",
        "label": "Focus adjustment",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 30,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "auto_focus_modes",
        "label": "Auto focus modes",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 31,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "auto_focus_points",
        "label": "Auto focus points",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 32,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "auto_focus_lock",
        "label": "Auto focus lock",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 33,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "auto_focus_assist_beam",
        "label": "Auto focus beam",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 34,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "iso_sensitivity",
        "label": "Iso sensitivity",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 35,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "light_exposure_modes",
        "label": "Light exposure modes",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 36,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "light_exposure_corrections",
        "label": "Light exposure corrections",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 37,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "light_metering",
        "label": "Light metering",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 38,
        "group": "Technical",
        "groupOrder": 2
      },
      {
        "name": "auto_exposure",
        "label": "Auto exposure",
        "choices": [
          {
            "label": "yes",
            "value": "1"
          },
          {
            "label": "no",
            "value": "0"
          }
        ],
        "enabled": false,
        "type": "boolean",
        "order": 39,
        "group": "Technical",
        "groupOrder": 2,
        "populateDefault": true
      },
      {
        "name": "iso_sensitivity_max",
        "label": "ISO sensitivity (max)",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 40,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "iso_sensitivity_min",
        "label": "ISO sensitivity (min)",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "number",
        "order": 41,
        "group": "Technical",
        "groupOrder": 2,
        "formatterOptions": {
          "decimals": 0,
          "grouping": false,
          "orderSeparator": "",
          "decimalSeparator": "."
        }
      },
      {
        "name": "tshirt_style",
        "label": "T-Shirt style",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 1,
        "group": "Design",
        "groupOrder": 3,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 55,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "container_material",
        "label": "Container material",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 1,
        "group": "Manufacturing",
        "groupOrder": 4,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 50,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "tshirt_materials",
        "label": "T-Shirt material",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 2,
        "group": "Manufacturing",
        "groupOrder": 4,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 51,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "main_color",
        "label": "Main color",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 1,
        "group": "Color",
        "groupOrder": 5,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 52,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "secondary_color",
        "label": "Secondary color",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 2,
        "group": "Color",
        "groupOrder": 5,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 53,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "clothing_size",
        "label": "Clothing size",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 1,
        "group": "Size",
        "groupOrder": 6,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 54,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "picture",
        "label": "Picture",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 1,
        "group": "Media",
        "groupOrder": 7
      },
      {
        "name": "pdf_description",
        "label": "PDF description",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 2,
        "group": "Media",
        "groupOrder": 7
      },
      {
        "name": "image",
        "label": "Model picture",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 11,
        "group": "Media",
        "groupOrder": 7
      },
      {
        "name": "notice",
        "label": "Notice",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 15,
        "group": "Media",
        "groupOrder": 7
      },
      {
        "name": "variation_image",
        "label": "Variant picture",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 20,
        "group": "Media",
        "groupOrder": 7
      },
      {
        "name": "ean",
        "label": "EAN",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 0,
        "group": "ERP",
        "groupOrder": 8
      },
      {
        "name": "sku",
        "label": "SKU",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "in list",
            "value": "in",
            "data": "in",
            "attr": []
          }
        ],
        "enabled": true,
        "type": "identifier",
        "order": 1,
        "group": "ERP",
        "groupOrder": 8
      },
      {
        "name": "supplier",
        "label": "Supplier",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 2,
        "group": "ERP",
        "groupOrder": 8,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 76,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "price",
        "label": "Price",
        "choices": [
          {
            "label": "=",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": ">=",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": ">",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "<=",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "<",
            "value": "5",
            "data": 5,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "price",
        "order": 6,
        "group": "ERP",
        "groupOrder": 8,
        "formatterOptions": {
          "decimals": 2,
          "grouping": true,
          "orderSeparator": ",",
          "decimalSeparator": "."
        },
        "currencies": {
          "EUR": "EUR",
          "USD": "USD"
        }
      },
      {
        "name": "erp_name",
        "label": "ERP name",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 10,
        "group": "ERP",
        "groupOrder": 8
      },
      {
        "name": "meta_description",
        "label": "Meta description",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 10,
        "group": "Ecommerce",
        "groupOrder": 9
      },
      {
        "name": "meta_title",
        "label": "Meta title",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 10,
        "group": "Ecommerce",
        "groupOrder": 9
      },
      {
        "name": "keywords",
        "label": "Keywords",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 20,
        "group": "Ecommerce",
        "groupOrder": 9
      },
      {
        "name": "color",
        "label": "Color",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 1,
        "group": "Product",
        "groupOrder": 10,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 69,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "size",
        "label": "Size",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 2,
        "group": "Product",
        "groupOrder": 10,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 70,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "eu_shoes_size",
        "label": "EU Shoes Size",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 3,
        "group": "Product",
        "groupOrder": 10,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 71,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "sole_composition",
        "label": "Sole composition",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 4,
        "group": "Product",
        "groupOrder": 10
      },
      {
        "name": "composition",
        "label": "Composition",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 5,
        "group": "Product",
        "groupOrder": 10
      },
      {
        "name": "wash_temperature",
        "label": "Wash temperature",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 6,
        "group": "Product",
        "groupOrder": 10,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 67,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      },
      {
        "name": "care_instructions",
        "label": "Care instructions",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 7,
        "group": "Product",
        "groupOrder": 10
      },
      {
        "name": "top_composition",
        "label": "Top composition",
        "choices": [
          {
            "label": "contains",
            "value": "1",
            "data": 1,
            "attr": []
          },
          {
            "label": "does not contain",
            "value": "2",
            "data": 2,
            "attr": []
          },
          {
            "label": "is equal to",
            "value": "3",
            "data": 3,
            "attr": []
          },
          {
            "label": "starts with",
            "value": "4",
            "data": 4,
            "attr": []
          },
          {
            "label": "is empty",
            "value": "empty",
            "data": "empty",
            "attr": []
          },
          {
            "label": "is not empty",
            "value": "not empty",
            "data": "not empty",
            "attr": []
          }
        ],
        "enabled": false,
        "type": "string",
        "order": 8,
        "group": "Product",
        "groupOrder": 10
      },
      {
        "name": "material",
        "label": "Material",
        "enabled": false,
        "choices": [],
        "type": "select2-choice",
        "order": 22,
        "group": "Product",
        "groupOrder": 10,
        "populateDefault": true,
        "choiceUrl": "pim_ui_ajaxentity_list",
        "choiceUrlParams": {
          "class": "Pim\\Bundle\\CatalogBundle\\Entity\\AttributeOption",
          "dataLocale": "en_US",
          "collectionId": 77,
          "options": {
            "type": "code"
          }
        },
        "emptyChoice": true
      }
    ]
  },
  "data": "{\"data\":[{\"identifier\":\"16466450\",\"image\":null,\"label\":\"Lenovo M0620\",\"family\":\"Loudspeakers\",\"enabled\":true,\"completeness\":50,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1229\",\"document_type\":\"product\",\"technical_id\":\"1229\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1229\",\"toggle_status_link\":\"\\/enrich\\/product\\/1229\\/toggle-status\"},{\"identifier\":\"13710067\",\"image\":null,\"label\":\"Fujifilm FinePix S4200\",\"family\":\"Digital cameras\",\"enabled\":true,\"completeness\":28,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1218\",\"document_type\":\"product\",\"technical_id\":\"1218\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1218\",\"toggle_status_link\":\"\\/enrich\\/product\\/1218\\/toggle-status\"},{\"identifier\":\"594877\",\"image\":null,\"label\":\"Xerox DocuMate 152\",\"family\":\"Scanners\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1207\",\"document_type\":\"product\",\"technical_id\":\"1207\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1207\",\"toggle_status_link\":\"\\/enrich\\/product\\/1207\\/toggle-status\"},{\"identifier\":\"14191635\",\"image\":null,\"label\":\"Samsung DV 300F\",\"family\":\"Digital cameras\",\"enabled\":true,\"completeness\":28,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1196\",\"document_type\":\"product\",\"technical_id\":\"1196\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1196\",\"toggle_status_link\":\"\\/enrich\\/product\\/1196\\/toggle-status\"},{\"identifier\":\"12830519\",\"image\":null,\"label\":\"HP LD4730G\",\"family\":\"PC Monitors\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1185\",\"document_type\":\"product\",\"technical_id\":\"1185\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1185\",\"toggle_status_link\":\"\\/enrich\\/product\\/1185\\/toggle-status\"},{\"identifier\":\"8617001\",\"image\":null,\"label\":\"Samsung 100UP\",\"family\":\"Camcorders\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1174\",\"document_type\":\"product\",\"technical_id\":\"1174\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1174\",\"toggle_status_link\":\"\\/enrich\\/product\\/1174\\/toggle-status\"},{\"identifier\":\"14962869\",\"image\":null,\"label\":\"GEAR4 Classic Red Bird\",\"family\":\"Loudspeakers\",\"enabled\":true,\"completeness\":50,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1163\",\"document_type\":\"product\",\"technical_id\":\"1163\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1163\",\"toggle_status_link\":\"\\/enrich\\/product\\/1163\\/toggle-status\"},{\"identifier\":\"127469\",\"image\":null,\"label\":\"HP LaserJet 1160 Printer\",\"family\":\"Laser and LED printers\",\"enabled\":true,\"completeness\":40,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1152\",\"document_type\":\"product\",\"technical_id\":\"1152\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1152\",\"toggle_status_link\":\"\\/enrich\\/product\\/1152\\/toggle-status\"},{\"identifier\":\"10977324\",\"image\":null,\"label\":\"Brother MFC-J5910DW multifunctional\",\"family\":\"Multifunctionals\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1145\",\"document_type\":\"product\",\"technical_id\":\"1145\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1145\",\"toggle_status_link\":\"\\/enrich\\/product\\/1145\\/toggle-status\"},{\"identifier\":\"3669355\",\"image\":null,\"label\":\"Trust MiDo 2.1 Speaker Set\",\"family\":\"Loudspeakers\",\"enabled\":true,\"completeness\":50,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1133\",\"document_type\":\"product\",\"technical_id\":\"1133\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1133\",\"toggle_status_link\":\"\\/enrich\\/product\\/1133\\/toggle-status\"},{\"identifier\":\"13689212\",\"image\":null,\"label\":\"Canon LEGRIA HF M52\",\"family\":\"Camcorders\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1134\",\"document_type\":\"product\",\"technical_id\":\"1134\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1134\",\"toggle_status_link\":\"\\/enrich\\/product\\/1134\\/toggle-status\"},{\"identifier\":\"1314976\",\"image\":{\"filePath\":\"c\\/d\\/b\\/9\\/cdb9217f900f11737e56f28fb093c2949c6f5c1e_1314976_5566.jpg\",\"originalFilename\":\"1314976-5566.jpg\"},\"label\":\"Sony SS-SP32FWB\",\"family\":\"Loudspeakers\",\"enabled\":true,\"completeness\":50,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1122\",\"document_type\":\"product\",\"technical_id\":\"1122\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1122\",\"toggle_status_link\":\"\\/enrich\\/product\\/1122\\/toggle-status\"},{\"identifier\":\"15705882\",\"image\":null,\"label\":\"Lexmark MS812dtn\",\"family\":\"Laser and LED printers\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1111\",\"document_type\":\"product\",\"technical_id\":\"1111\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1111\",\"toggle_status_link\":\"\\/enrich\\/product\\/1111\\/toggle-status\"},{\"identifier\":\"7820677\",\"image\":null,\"label\":\"Microsoft LifeCam Studio for Business\",\"family\":\"Webcams\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1100\",\"document_type\":\"product\",\"technical_id\":\"1100\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1100\",\"toggle_status_link\":\"\\/enrich\\/product\\/1100\\/toggle-status\"},{\"identifier\":\"1325806\",\"image\":null,\"label\":\"Kodak EasyShare ZD710\",\"family\":\"Digital cameras\",\"enabled\":true,\"completeness\":28,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1089\",\"document_type\":\"product\",\"technical_id\":\"1089\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1089\",\"toggle_status_link\":\"\\/enrich\\/product\\/1089\\/toggle-status\"},{\"identifier\":\"8009612\",\"image\":null,\"label\":\"Kodak i2400\",\"family\":\"Scanners\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1078\",\"document_type\":\"product\",\"technical_id\":\"1078\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1078\",\"toggle_status_link\":\"\\/enrich\\/product\\/1078\\/toggle-status\"},{\"identifier\":\"1320064\",\"image\":null,\"label\":\"Lenovo Flat Panel Performance ThinkVision L190x\",\"family\":\"PC Monitors\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1067\",\"document_type\":\"product\",\"technical_id\":\"1067\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1067\",\"toggle_status_link\":\"\\/enrich\\/product\\/1067\\/toggle-status\"},{\"identifier\":\"11275331\",\"image\":null,\"label\":\"Viewsonic Value Series VA2703\",\"family\":\"PC Monitors\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1056\",\"document_type\":\"product\",\"technical_id\":\"1056\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1056\",\"toggle_status_link\":\"\\/enrich\\/product\\/1056\\/toggle-status\"},{\"identifier\":\"10627329\",\"image\":{\"filePath\":\"4\\/5\\/9\\/e\\/459e0fd33ea6d14d7749bb4a73e0e63bfe59af51_10627329_7290.jpg\",\"originalFilename\":\"10627329-7290.jpg\"},\"label\":\"NEC EX201W\",\"family\":\"PC Monitors\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1045\",\"document_type\":\"product\",\"technical_id\":\"1045\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1045\",\"toggle_status_link\":\"\\/enrich\\/product\\/1045\\/toggle-status\"},{\"identifier\":\"14101037\",\"image\":null,\"label\":\"Toshiba 23DL933G LED TV\",\"family\":\"LED TVs\",\"enabled\":true,\"completeness\":75,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1034\",\"document_type\":\"product\",\"technical_id\":\"1034\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1034\",\"toggle_status_link\":\"\\/enrich\\/product\\/1034\\/toggle-status\"},{\"identifier\":\"1712634\",\"image\":null,\"label\":\"Sony NWZ-E438F\",\"family\":\"MP3 players\",\"enabled\":true,\"completeness\":50,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1022\",\"document_type\":\"product\",\"technical_id\":\"1022\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1022\",\"toggle_status_link\":\"\\/enrich\\/product\\/1022\\/toggle-status\"},{\"identifier\":\"16672632\",\"image\":null,\"label\":\"HP KQ245AA\",\"family\":\"Webcams\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1023\",\"document_type\":\"product\",\"technical_id\":\"1023\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1023\",\"toggle_status_link\":\"\\/enrich\\/product\\/1023\\/toggle-status\"},{\"identifier\":\"11704300\",\"image\":null,\"label\":\"Canon i-SENSYS MF4410\",\"family\":\"Multifunctionals\",\"enabled\":true,\"completeness\":66,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1011\",\"document_type\":\"product\",\"technical_id\":\"1011\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1011\",\"toggle_status_link\":\"\\/enrich\\/product\\/1011\\/toggle-status\"},{\"identifier\":\"12249740\",\"image\":{\"filePath\":\"9\\/3\\/f\\/d\\/93fdc388fd0acd8c37abe84c4567d0a2dc223f81_12249740_4511.jpg\",\"originalFilename\":\"12249740-4511.jpg\"},\"label\":\"Avision AV36\",\"family\":\"Scanners\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_1000\",\"document_type\":\"product\",\"technical_id\":\"1000\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/1000\",\"toggle_status_link\":\"\\/enrich\\/product\\/1000\\/toggle-status\"},{\"identifier\":\"13526654\",\"image\":null,\"label\":\"Philips 17S1AB\",\"family\":\"PC Monitors\",\"enabled\":true,\"completeness\":60,\"created\":\"07\\/16\\/2018\",\"updated\":\"07\\/16\\/2018\",\"complete_variant_products\":null,\"id\":\"product_989\",\"document_type\":\"product\",\"technical_id\":\"989\",\"delete_link\":\"\\/enrich\\/product\\/rest\\/989\",\"toggle_status_link\":\"\\/enrich\\/product\\/989\\/toggle-status\"}],\"totalRecords\":1049,\"options\":{\"totalRecords\":null}}"
})
                    resolve()
                })

                console.timeEnd('setDatagridState')
                return promise
                // return $.get(
                //     Routing.generate(datagridLoadUrl, params),
                //     this.loadDataGrid.bind(this)
                // );
            },

            /**
             * @inheritdoc
             */
            render() {
                console.time('renderGrid')
                this.$el.empty()
                // .append(this.loadingMask.$el);
                // this.loadingMask.render().show();

                // $.when(this.getDefaultColumns(), this.getDefaultView())
                //     .then((defaultColumns, defaultView) => {
                const state = this.setDatagridState(["identifier","image","label","family","enabled","completeness","created","updated","complete_variant_products"], {"view":null});

                console.timeEnd('renderGrid')
                return state;
                    // });
            }
        });
    }
);
