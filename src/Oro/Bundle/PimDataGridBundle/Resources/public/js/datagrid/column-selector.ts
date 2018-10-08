import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import * as Backbone from 'backbone';

const mediator = require('oro/mediator');
const __ = require('oro/translator');
const Routing = require('routing');
const DatagridState = require('pim/datagrid/state');

class ColumnSelector extends BaseView {
  public config: any;
  public datagridCollection: Backbone.Collection<any>;
  public loadedAttributeGroups: any[];
  public loadedColumns: any[];
  public modal: any;

  public buttonTemplate = `<div class="AknGridToolbar-right"><div class="AknGridToolbar-actionButton">
  <a class="AknActionButton" title="Columns" data-open>Columns</a></div></div>`;

  public modalTemplate = `<div class="AknFullPage-upperTitle">
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

  public columnsTemplate = `
    <ul id="column-list" class="AknVerticalList connected-sortable">
        <% _.each(columns, function(column) { %>
          <li class="AknVerticalList-item AknVerticalList-item--movable" data-value="<%- column.code %>" data-group="<%- column.group %>">
              <div><%- column.label %></div>
          </li>
        <% }); %>
    </ul>
  `;

  public selectedTemplate = `
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

    this.loadedAttributeGroups = [];
    this.config = {...this.config, ...options.config};
  }

  configure() {
    mediator.once('grid_load:start', this.setDatagridCollection.bind(this));

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setDatagridCollection(datagridCollection: Backbone.Collection<any>) {
    this.datagridCollection = datagridCollection;
  }

  // @TODO - Change to correct endpoint after it's implemented
  fetchAttributeGroups(): any {
    if (0 === this.loadedAttributeGroups.length) {
      return $.get('/rest/attribute-group').then((groups: any) => {
        groups = _.map(groups, (group: any, key) => {
          group.label = group.labels.en_US
          group.code = key
          group.children = group.attributes.length

          return group;
        })

        this.loadedAttributeGroups = groups;

        return groups;
      })
    }

    return new Promise(resolve => resolve(this.loadedAttributeGroups))
  }

  fetchByAttributeGroup(event: JQuery.Event) {
    this.modal.$el.find('[data-attributes] [data-group]').removeClass('active');
    $(event.currentTarget).addClass('active');
    this.fetchColumns();
  }

  // @TODO - Add caching
  fetchColumns() {
    const search = this.modal.$el.find('input[type="search"]').val().trim();
    const group = this.modal.$el.find('.active[data-group]').data('value');
    const url = Routing.generate('pim_datagrid_productgrid_available_columns');
    const params = $.param(_.omit({search, group}, _.isEmpty));
    return $.get(`${url}?${params}`);
  }

  render() {
    this.$el.html(_.template(this.buttonTemplate));

    return this;
  }

  getInitialColumns(columns: any[]) {
    const selectedColumns = DatagridState.get('product-grid', 'columns');
    const datagridColumns = selectedColumns.split(',')

    return _.map(columns, (column: any) => {
      column.selected = datagridColumns.includes(column.code);
      column.removable = undefined !== column.group

      return column;
    })
  }

  renderColumns() {
    const columns = this.loadedColumns;
    const unSelectedColumns = _.where(columns, {selected: false});
    const selectedColumns = _.where(columns, {selected: true});

    this.modal.$el
      .find('[data-columns]')
      .empty()
      .append(_.template(this.columnsTemplate)({columns: unSelectedColumns}));

    this.modal.$el
      .find('[data-columns-selected]')
      .empty()
      .append(_.template(this.selectedTemplate)({columns: selectedColumns}));

    this.modal.$el.on('click', '#column-selection .action', this.unselectColumn.bind(this));
  }

  unselectColumn(event: JQuery.Event) {
    const code = $(event.currentTarget) .parents('[data-value]') .data('value');

    this.loadedColumns = _.map(this.loadedColumns, column => {
      if (column.code === code) {
        column.selected = false;
      }

      return column;
    });

    this.renderColumns();
    this.setSortable();
  }

  openModal() {
    this.fetchAttributeGroups().then(groups => {
      console.log('fetchAttributeGroups', groups)
      const modal = new Backbone.BootstrapModal({
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

      this.modal = modal;
      this.modal.$el.on('keyup', 'input[type="search"]', this.searchColumns.bind(this));
      this.modal.$el.on('click', '[data-attributes] [data-group]', this.fetchByAttributeGroup.bind(this));

      this.fetchColumns().then(columns => {
        this.loadedColumns = this.getInitialColumns(columns);
        this.renderColumns()
        this.setSortable();
      });
    });
  }

  setSortable() {
    this.modal.$el
      .find('#column-list, #column-selection')
      .sortable({
        connectWith: '.connected-sortable',
        containment: this.modal.$el,
        tolerance: 'pointer',
        cursor: 'move',
        cancel: 'div.alert',
        receive: (event: any, ui: any) => {
          console.log(event, ui);
          // var model = _.first(this.collection.where({code: ui.item.data('value')}));
          // model.set('displayed', ui.sender.is('#column-list') && model.get('removable'));

          // if (!model.get('removable')) {
          //     $(ui.sender).sortable('cancel');
          // } else {
          //     this.validateSubmission();
          // }
        },
      }).disableSelection();
  }

  searchColumns(event: JQuery.Event) {
    console.log('searchColumns', event);
  }

  applyColumns() {}
}

export = ColumnSelector;
