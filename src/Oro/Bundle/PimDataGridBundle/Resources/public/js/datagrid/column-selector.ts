import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import * as Backbone from 'backbone';

const mediator = require('oro/mediator');
const __ = require('oro/translator');
const Routing = require('routing');
const DatagridState = require('pim/datagrid/state');

interface AttributeGroup {
  label: string;
  labels: any;
  code: string;
  attributes: string[];
  children: number;
}

interface Column {
  code: string;
  selected: boolean;
  removable: boolean;
}

class ColumnSelector extends BaseView {
  public config: any;
  public datagridCollection: Backbone.Collection<any>;
  public loadedAttributeGroups: {[name: string]: AttributeGroup};
  public loadedColumns: {[name: string]: Column};
  public modal: any;
  public debounceSearchTimer: any;

  public buttonTemplate: string = `<div class="AknGridToolbar-right"><div class="AknGridToolbar-actionButton">
  <a class="AknActionButton" title="Columns" data-open>Columns</a></div></div>`;

  public modalTemplate: string = `<div class="AknFullPage-upperTitle">
    <div class="AknFullPage-title"> Configure columns </div>
    <div class="AknFullPage-description">
        Select the information you want to display as column
    </div>
  </div>
  <div id="column-configurator"><div class="AknColumnConfigurator">
    <div class="AknColumnConfigurator-column AknColumnConfigurator-column--gray">
      <div class="AknColumnConfigurator-columnHeader">Attribute groups</div>
      <div class="AknColumnConfigurator-listContainer" data-attributes>
        <ul class="AknVerticalList nav-list">
          <li class="AknVerticalList-item AknVerticalList-item--selectable tab active" data-group data-value="">
              All Groups
          </li>
          <% _.each(groups, (group) => { %>
            <li class="AknVerticalList-item AknVerticalList-item--selectable tab" data-group data-value="<%- group.code %>">
                <%- group.label %>
                <span class="AknBadge"><%- group.children %></span>
            </li>
          <% }) %>
        </ul>
      </div>
    </div>
    <div class="AknColumnConfigurator-column">
      <div class="AknColumnConfigurator-columnHeader"> <input class="AknTextField AknColumnConfigurator-searchInput" type="search" placeholder="<%- _.__('pim_datagrid.column_configurator.search') %>"/> </div>
      <div class="AknColumnConfigurator-listContainer" data-columns></div>
    </div>
    <div class="AknColumnConfigurator-column">
        <div class="AknColumnConfigurator-columnHeader">Selected Columns
          <button class="AknButton AknButton--grey reset"> Clear </button>
         </div>
         <div class="AknColumnConfigurator-listContainer" data-columns-selected></div>
    </div>
  </div>
  </div>
`;

  public columnsTemplate: string = `
    <ul id="column-list" class="AknVerticalList connected-sortable">
        <% _.each(columns, function(column) { %>
          <li class="AknVerticalList-item AknVerticalList-item--movable" data-value="<%- column.code %>" data-group="<%- column.group %>">
              <div><%- column.label %></div>
          </li>
        <% }); %>
    </ul>
  `;

  public selectedTemplate: string = `
    <ul id="column-selection" class="AknVerticalList connected-sortable ui-sortable">
        <% _.each(columns, (column) => { %>
          <li class="AknVerticalList-item AknVerticalList-item--movable" data-value="<%- column.code %>" data-group="<%- column.group %>">
            <div><%- column.label %></div>
            <% if (column.removable) { %>
              <div class="AknVerticalList-delete action" title="<%- _.__('pim_datagrid.column_configurator.remove_column') %>"></div>
            <% } %>
          </li>
        <% }) %>
        <div class="AknMessageBox AknMessageBox--error AknMessageBox--hide alert alert-error"><%- _.__('pim_datagrid.column_configurator.min_message') %></div>
    </ul>
  `;

  public events(): Backbone.EventsHash {
    return {
      'click [data-open]': 'openModal',
    };
  }

  constructor(options: {config: any}) {
    super({...options});

    this.loadedAttributeGroups = {};
    this.loadedColumns = {};
    this.config = {...this.config, ...options.config};
  }

  configure() {
    mediator.once('grid_load:start', (datagridCollection: Backbone.Collection<any>) => {
      this.datagridCollection = datagridCollection;
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  render(): BaseView {
    this.$el.html(_.template(this.buttonTemplate));

    return this;
  }

  // @TODO - Change to correct endpoint after it's implemented
  fetchAttributeGroups(): PromiseLike<{[name: string]: AttributeGroup}> {
    if (_.isEmpty(this.loadedAttributeGroups)) {
      return $.get('/rest/attribute-group').then((groups: {[name: string]: AttributeGroup}) => {
        const loadedAttributeGroups = _.mapObject(groups, (group: AttributeGroup) => {
          group.label = group.labels.en_US;
          group.children = group.attributes.length;

          return group;
        });

        this.loadedAttributeGroups = loadedAttributeGroups;

        return groups;
      });
    }

    return new Promise(resolve => resolve(this.loadedAttributeGroups));
  }

  fetchColumns(): PromiseLike<{[name: string]: Column}> {
    console.log('fetchColumns')
    const search = this.modal.$el.find('input[type="search"]').val().trim();
    const group = this.modal.$el.find('.active[data-group]').data('value');
    const url = Routing.generate('pim_datagrid_productgrid_available_columns');
    const params = $.param(_.omit({search, group}, _.isEmpty));

    return $.get(`${url}?${params}`);
  }

  mergeFetchedColumns(fetchedColumns: {[name: string]: Column}) {
    console.log('mergeFetchedColumns', this.loadedColumns, fetchedColumns)
    // fetchedColumns = _.map(fetchedColumns, (column: Column) => {
    //   column.selected = _.find(this.loadedColumns, { code: column.code }).selected || false

    //   return column
    // })


    // Get the new columns
    // Set selected status from the ones already loaded
    // Merge the two arrays keeping the selected status
    // Re-render both lists and set sortable again
  }

  filterByAttributeGroup(event: JQuery.Event): void {
    this.modal.$el.find('[data-attributes] [data-group]').removeClass('active');
    $(event.currentTarget).addClass('active');
    this.fetchColumns().then(this.mergeFetchedColumns.bind(this));
  }

  clearSearch() {
    this.modal.$el.find('input[type="search"]').val('').trigger('keyup')
  }

  debounceSearch(event: JQuery.Event): void {
    if (null !== this.debounceSearchTimer) {
      clearTimeout(this.debounceSearchTimer);
    }

    if (27 === event.keyCode) {
      return this.clearSearch();
    }

    if (13 === event.keyCode) {
      this.fetchColumns().then(this.mergeFetchedColumns.bind(this))
    } else {
      this.debounceSearchTimer = setTimeout(() => {
        this.fetchColumns().then(this.mergeFetchedColumns.bind(this))
      }, 200);
    }
  }

  // @TODO - Sort by position
  setColumnsSelectedByDefault(columns: {[name: string]: Column}) {
    const selectedColumns = DatagridState.get('product-grid', 'columns');
    const datagridColumns = selectedColumns.split(',');

    console.log('columns before', columns)

    const blah =_.mapObject(columns, (column: Column) => {
      column.selected = datagridColumns.includes(column.code);
      column.removable = true;

      return column;
    });

    console.log('columns after', blah)
    return blah;
  }

  renderColumns(): void {
    const unSelectedColumns = this.getColumnsBySelectedStatus(false)
    const selectedColumns = this.getColumnsBySelectedStatus()

    console.log('unSelectedColumns', unSelectedColumns)
    console.log('selectedColumns', selectedColumns)

    this.modal.$el
      .find('[data-columns]')
      .empty()
      .append(_.template(this.columnsTemplate)({columns: unSelectedColumns}));

    this.modal.$el
      .find('[data-columns-selected]')
      .empty()
      .append(_.template(this.selectedTemplate)({columns: selectedColumns}));

    this.modal.$el.on('click', '#column-selection .action', this.unselectColumn.bind(this));
    this.setValidation();
  }

  setColumnStatus(code: string, selected = true): void {
    this.loadedColumns = _.mapObject(this.loadedColumns, (column: Column) => {
      if (column.code === code) {
        column.selected = selected;
      }

      return column;
    });
  }

  unselectColumn(event: JQuery.Event): void {
    const column = $(event.currentTarget).parent();
    const code = $(event.currentTarget)
      .parents('[data-value]')
      .data('value');
    column.appendTo(this.modal.$el.find('#column-list'));
    this.setColumnStatus(code, false);
    this.setValidation();
  }

  setValidation(): void {
    const selectedColumns = this.getColumnsBySelectedStatus()
    const showValidationError = selectedColumns.length === 0;
    const error = this.modal.$el.find('#column-selection .alert-error');

    if (showValidationError) {
      return error.show();
    }

    return error.hide();
  }

  clearAllColumns(): void {
    this.loadedColumns = _.mapObject(this.loadedColumns, (column: Column) => {
      column.selected = false;

      return column;
    });

    this.renderColumns();
    this.setSortable();
  }

  openModal(): void {
    if (this.modal) {
      this.modal.$el.off();
      this.modal.close();
      this.modal.remove();
    }

    this.fetchAttributeGroups().then(groups => {
      const modal = new (<any>Backbone).BootstrapModal({
        className: 'modal modal--fullPage modal--topButton column-configurator-modal',
        modalOptions: {backdrop: 'static', keyboard: false},
        allowCancel: true,
        okCloses: false,
        cancelText: __('pim_common.cancel'),
        title: __('pim_datagrid.column_configurator.title'),
        content: _.template(this.modalTemplate)({groups}),
        okText: __('pim_common.apply'),
      });

      modal.open();
      modal.on('ok', this.saveColumnsToDatagridState.bind(this));

      this.modal = modal;
      this.modal.$el.on('keyup', 'input[type="search"]', this.debounceSearch.bind(this));
      this.modal.$el.on('click', '[data-attributes] [data-group]', this.filterByAttributeGroup.bind(this));
      this.modal.$el.on('click', '.reset', this.clearAllColumns.bind(this));

      this.fetchColumns().then((columns: {[name: string]: Column}) => {
        this.loadedColumns = this.setColumnsSelectedByDefault(columns);
        console.log('set loaded columns first time', this.loadedColumns)
        this.renderColumns();
        this.setSortable();
      });
    });
  }

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
          this.setValidation();
        },
      })
      .disableSelection();
  }

  getColumnsBySelectedStatus(selected = true) {
    return _.pick(this.loadedColumns, (column: Column) => column.selected === selected)
  }

  saveColumnsToDatagridState(): void {
    const selectedColumns = this.getColumnsBySelectedStatus()
    const selected: string = _.map(selectedColumns, 'code').join();

    if (!selected.length) {
      return;
    }

    DatagridState.set('product-grid', 'columns', selected);
    this.modal.close();

    // Reload the page
    var url = window.location.hash;
    (<any>Backbone.history).fragment = new Date().getTime();
    Backbone.history.navigate(url, true);
  }
}

export = ColumnSelector;
