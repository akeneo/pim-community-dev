import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import * as Backbone from 'backbone';

const mediator = require('oro/mediator');
const __ = require('oro/translator');
const Routing = require('routing');
const DatagridState = require('pim/datagrid/state');
const buttonTemplate = require('pim/template/datagrid/column-selector/button');
const columnsTemplate = require('pim/template/datagrid/column-selector/columns');
const innerModalTemplate = require('pim/template/datagrid/column-selector/modal');
const selectedTemplate = require('pim/template/datagrid/column-selector/selected');
const modalTemplate = require('pim/template/common/modal-centered');

interface AttributeGroup {
  code: string;
  label: string;
  count: number;
}

interface Column {
  name: string;
  code: string;
  selected: boolean;
  removable: boolean;
  sortOrder: number;
  displayed: boolean;
}

interface DatagridCollection extends Backbone.Collection<any> {
  decodeStateData: (name: string) => any;
}

class ColumnSelector extends BaseView {
  public attributeGroupSelector: string;
  public config: any;
  public datagridCollection: DatagridCollection;
  public datagridElement: any;
  public debounceSearchTimer: any;
  public loadedAttributeGroups: AttributeGroup[];
  public loadedColumns: {[name: string]: Column};
  public locale: string;
  public modal: any;
  public page: number = 1;
  public searchInputSelector: string;
  public hideButton: boolean;

  private buttonTemplate: ((...data: any[]) => string) = _.template(buttonTemplate);
  private innerModalTemplate: ((...data: any[]) => string) = _.template(innerModalTemplate);
  private columnsTemplate: ((...data: any[]) => string) = _.template(columnsTemplate);
  private selectedTemplate: ((...data: any[]) => string) = _.template(selectedTemplate);
  private modalTemplate: ((...data: any[]) => string) = _.template(modalTemplate);

  public events(): Backbone.EventsHash {
    return {
      'click [data-open]': 'openModal',
    };
  }

  constructor(options: {config: any}) {
    super({...options, ...{className: 'AknGridToolbar-right'}});

    this.loadedAttributeGroups = [];
    this.loadedColumns = {};
    this.searchInputSelector = 'input[type="search"]';
    this.attributeGroupSelector = '[data-attributes] [data-group]';
    this.config = {...this.config, ...options.config};
  }

  /**
   * When the grid loads, store the datagrid collection and the locale
   */
  configure() {
    mediator.once('datagrid_collection_set_after', (datagridCollection: any, datagridElement: any) => {
      this.datagridCollection = datagridCollection;
      this.datagridElement = datagridElement;
      this.hideButton = false === this.datagridElement.data('metadata').options.manageColumns;
      this.locale = this.getLocale();
      this.renderColumnSelector();
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * Get the local from the datagrid
   */
  getLocale(): string {
    const url = (<string>this.datagridCollection.url).split('?')[1];
    const urlParams = this.datagridCollection.decodeStateData(url);
    const datagridParams = urlParams['product-grid'] || {};

    return urlParams['dataLocale'] || datagridParams['dataLocale'];
  }

  /**
   * Render the 'columns' button
   */
  renderColumnSelector(): BaseView {
    if (true === this.hideButton) return this;

    this.$el.html(this.buttonTemplate({label: __('pim_datagrid.column_configurator.label')}));

    return this;
  }

  /**
   * Fetch the attribute groups for the columns and cache them
   */
  fetchAttributeGroups(): PromiseLike<AttributeGroup[]> {
    const url = Routing.generate('pim_datagrid_productgrid_available_columns_groups', {locale: this.locale});

    if (_.isEmpty(this.loadedAttributeGroups)) {
      return $.get(url).then((groups: AttributeGroup[]) => {
        this.loadedAttributeGroups = groups;

        return groups;
      });
    }

    return new Promise(resolve => resolve(this.loadedAttributeGroups));
  }

  /**
   * Fetch the columns with these possible parameters:
   *
   * search - The current search term
   * attribute_group - The current selected attribute group
   * locale - The datagrid locale
   * page - The page number (can be reset)
   *
   */
  fetchColumns(reset?: boolean): PromiseLike<{[name: string]: Column}> {
    const search = this.modal.$el
      .find(this.searchInputSelector)
      .val()
      .trim();
    const group = this.modal.$el.find('.active[data-group]').data('value');
    const url = Routing.generate('pim_datagrid_productgrid_available_columns');

    if (true === reset) {
      this.page = 1;
    }

    const params = $.param(
      _.omit(
        {
          search,
          attribute_group: group,
          page: this.page,
          locale: this.locale,
        },
        (param: any) => !param
      )
    );

    return $.get(`${url}?${params}`);
  }

  /**
   * Add the properties used by the front to the loaded columns
   */
  normalizeColumn(column: Column) {
    const storedColumn = this.loadedColumns[column.code];
    column.selected = false;

    if (storedColumn) {
      column.selected = storedColumn.selected;
      column.sortOrder = storedColumn.sortOrder;
    }

    column.removable = true;

    return column;
  }

  /**
   * Fetch the first page of columns and merge them with the selected ones
   */
  fetchColumnsWithSelected() {
    this.fetchColumns(true).then(columns => {
      this.loadedColumns = Object.assign(
        _.mapObject(columns, this.normalizeColumn.bind(this)),
        this.getColumnsBySelected()
      );
      this.renderColumns();
    });
  }

  /**
   * When an attribute group is clicked, set it as active and trigger a fetch
   */
  filterByAttributeGroup(event: JQueryEventObject): void {
    this.modal.$el.find(this.attributeGroupSelector).removeClass('active');
    $(event.currentTarget).addClass('active');
    this.fetchColumnsWithSelected();
  }

  /**
   * Clear the search input and trigger a fetch
   */
  clearSearch() {
    this.modal.$el
      .find(this.searchInputSelector)
      .val('')
      .trigger('keyup');
  }

  /**
   * Executes the search after a timeout
   */
  debounceSearch(event: JQueryEventObject): void {
    if (null !== this.debounceSearchTimer) {
      clearTimeout(this.debounceSearchTimer);
    }

    if (27 === event.keyCode) {
      return this.clearSearch();
    }

    if (13 === event.keyCode) {
      this.fetchColumnsWithSelected();
    } else {
      this.debounceSearchTimer = setTimeout(this.fetchColumnsWithSelected.bind(this), 300);
    }
  }

  /**
   * Get the selected columns from the datagrid metadata and merge them with the passed ones, keeping the selected state
   */
  setColumnsSelectedByDefault(columns: {[name: string]: Column}) {
    const metadataColumns = this.datagridElement.data('metadata').columns;
    const datagridColumns: {[name: string]: Column} = {};

    _.each(Object.assign(columns, metadataColumns), (column: Column) => {
      const columnNames = metadataColumns.map((column: Column) => String(column.name));
      const label = String(column.name || column.code);
      const data = {code: label, selected: columnNames.includes(label), sortOrder: columnNames.indexOf(label), removable: true};
      datagridColumns[label] = Object.assign(column, data);
    });

    return datagridColumns;
  }

  /**
   * Render the two sections for searchable columns and the selected ones
   */
  renderColumns(): void {
    const unSelectedColumns = this.getColumnsBySelected(false);
    const selectedColumns = this.getColumnsBySelected();

    this.modal.$el
      .find('[data-columns]')
      .empty()
      .append(this.columnsTemplate({columns: unSelectedColumns}));

    const sortedColumns = _.map(selectedColumns, (column: Column) => column).sort((a: Column, b: Column) => {
      return a.sortOrder - b.sortOrder;
    });

    this.modal.$el
      .find('[data-columns-selected]')
      .empty()
      .append(this.selectedTemplate({columns: sortedColumns}));

    this.modal.$el.on('click', '#column-selection .action', this.unselectColumn.bind(this));
    this.setSortable();
    this.setValidation();
    this.listenToListScroll();

    const scrollColumn = this.modal.$el.find('[data-columns]');
    const scrollHeight = scrollColumn.get(0).scrollHeight;
    const columnHeight = scrollColumn.outerHeight();

    if (scrollHeight === columnHeight) {
      this.fetchNextColumns(true);
    }
  }

  /**
   * Listen to the scroll for the searchable columns
   */
  listenToListScroll() {
    this.modal.$el
      .find('[data-columns]')
      .off('scroll')
      .on('scroll', this.fetchNextColumns.bind(this));
  }

  /**
   * Stop listening to the scroll event
   */
  stopListeningToListScroll() {
    this.modal.$el
      .find('[data-columns]')
      .removeClass('more')
      .off();
  }

  /**
   * Handle the infinite scroll - if the user scrolls to the bottom of the page, trigger
   * loading the next page of results
   */
  fetchNextColumns(loadNextPage: boolean = false): void {
    const list: any = this.modal.$el.find('[data-columns]').get(0);
    const scrollPosition = Math.max(0, list.scrollTop);
    const bottomPosition = list.scrollHeight - list.offsetHeight;
    const isBottom = bottomPosition === scrollPosition;

    if (isBottom || true === loadNextPage) {
      this.page = this.page + 1;

      this.fetchColumns().then((columns: {[name: string]: Column}) => {
        if (_.isEmpty(columns)) {
          return this.stopListeningToListScroll();
        }

        const mergedColumns = _.mapObject(columns, this.normalizeColumn.bind(this));
        this.loadedColumns = Object.assign(this.loadedColumns, mergedColumns);
        this.renderColumns();
      });
    }
  }

  /**
   * Set all columns selected to true or false given a matching code
   */
  setColumnStatus(code: string, selected = true): void {
    this.loadedColumns = _.mapObject(this.loadedColumns, (column: Column) => {
      if (column.code === String(code)) {
        column.selected = selected;
      }

      return column;
    });
  }

  /**
   * When the user clicks 'x' on a selected column, move it to the correct list and
   * display the validation error if necessary
   */
  unselectColumn(event: JQueryEventObject): void {
    const column = $(event.currentTarget).parent();
    const code = $(event.currentTarget)
      .parents('[data-value]')
      .data('value');
    column.appendTo(this.modal.$el.find('#column-list'));
    this.setColumnStatus(code, false);
    this.setValidation();
  }

  /**
   * When the selected column list is empty show the validation error
   */
  setValidation(): void {
    const selectedColumns = this.getColumnsBySelected();
    const showValidationError = _.isEmpty(selectedColumns);
    const error = this.modal.$el.find('#column-selection .alert-error');

    if (showValidationError) {
      return error.show();
    }

    return error.hide();
  }

  /**
   * Set all the columns selected to false when the user clicks on 'Clear'
   */
  clearAllColumns(): void {
    this.loadedColumns = _.mapObject(this.loadedColumns, (column: Column) => {
      column.selected = false;

      return column;
    });

    this.renderColumns();
  }

  /**
   * Fetch the attribute groups, open the modal and start listening to the search/click events
   */
  openModal(): void {
    this.page = 1;

    if (this.modal) {
      this.modal.$el.off();
      this.modal.close();
      this.modal.remove();
    }

    this.fetchAttributeGroups().then(groups => {
      const modal = new (<any>Backbone).BootstrapModal({
        okCloses: false,
        title: __('pim_datagrid.column_configurator.title'),
        innerDescription: __('pim_datagrid.column_configurator.description'),
        okText: __('pim_common.apply'),
        template: this.modalTemplate,
        innerClassName: 'AknFullPage--full',
        content: this.innerModalTemplate({
          groups,
          attributeGroupsLabel: __('pim_enrich.entity.attribute_group.plural_label'),
        }),
      });

      modal.open();
      modal.on('ok', this.saveColumnsToDatagridState.bind(this));

      this.modal = modal;
      this.modal.$el.on('keyup search', this.searchInputSelector, this.debounceSearch.bind(this));
      this.modal.$el.on('click', this.attributeGroupSelector, this.filterByAttributeGroup.bind(this));
      this.modal.$el.on('click', '.reset', this.clearAllColumns.bind(this));

      this.fetchColumns().then((columns: {[name: string]: Column}) => {
        this.loadedColumns = this.setColumnsSelectedByDefault(columns);
        this.renderColumns();
      });
    });
  }

  /**
   * Set the selected columns list as sortable and save the sort order
   */
  setSortable(): void {
    this.modal.$el
      .find('#column-list, #column-selection')
      .sortable({
        connectWith: '.connected-sortable',
        containment: this.modal.$el,
        tolerance: 'pointer',
        cursor: 'move',
        cancel: 'div.alert',
        receive: (_: any, ui: any) => {
          const code = ui.item.data('value');
          const senderIsColumn = ui.sender.is('#column-list');

          this.setColumnStatus(code, senderIsColumn);
          this.setColumnSortOrder();
          this.setValidation();
        },
      })
      .disableSelection();

    this.setColumnSortOrder();
  }

  /**
   * Get the column sort order from the DOM element and apply it to the loaded columns
   */
  setColumnSortOrder(): void {
    this.loadedColumns = _.mapObject(this.loadedColumns, (column: Column) => {
      const sortOrder = this.modal.$el.find(`#column-selection [data-value="${column.code}"]`).index();

      if (sortOrder > -1) {
        column.sortOrder = sortOrder;
      }

      return column;
    });
  }

  /**
   * Get a list of the selected columns (from all the loaded ones)
   */
  getColumnsBySelected(selected = true): {[name: string]: Column} {
    return _.pick(this.loadedColumns, (column: Column) => column.selected === selected);
  }

  /**
   * Save the list of selected columns (and their sort order) to the datagrid and reload the page
   */
  saveColumnsToDatagridState(): void {
    this.setColumnSortOrder();

    const columns = this.getColumnsBySelected();
    const selected = Object.keys(columns)
      .sort((a, b) => {
        return columns[a].sortOrder - columns[b].sortOrder;
      })
      .join()
      .trim();

    if (!selected.length) {
      return;
    }

    DatagridState.set('product-grid', 'columns', selected);
    this.modal.close();

    var url = window.location.hash;
    (<any>Backbone.history).fragment = new Date().getTime();
    Backbone.history.navigate(url, true);
  }
}

export = ColumnSelector;
