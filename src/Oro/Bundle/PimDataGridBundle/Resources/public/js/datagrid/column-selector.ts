import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import * as Backbone from 'backbone';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

class ColumnSelector extends BaseView {
  public config: any;
  public datagridCollection: Backbone.Collection<any>;
  public loadedAttributeGroups: any[];
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
      <div class="AknColumnConfigurator-listContainer" data-attributes></div>
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

  public attributeTemplate = `<ul class="AknVerticalList nav-list">
      <% _.each(groups, (group) => { %>
        <li class="AknVerticalList-item AknVerticalList-item--selectable tab" data-value="<%- group.code %>">
            <%- group.label %>
            <span class="AknBadge"><%- group.children %></span>
        </li>
      <% }) %>
    </ul>`;

  public columnsTemplate = `
    <ul id="column-list" class="AknVerticalList connected-sortable">
        <% _.each(columns, function(column) { %>
          <li class="AknVerticalList-item AknVerticalList-item--movable" data-value="<%- column.code %>" data-group="<%- column.group %>">
              <div><%- column.label %></div>
          </li>
        <% }); %>
    </ul>
  `

  public selectedTemplate = `
    <ul id="column-selection" class="AknVerticalList connected-sortable ui-sortable">
        <% _.each(columns, (column) => { %>
          <li class="AknVerticalList-item AknVerticalList-item--movable ui-sortable-handle" data-value="<%- column.code %>" data-group="<%- column.group  %>">
              <div><%- column.label %></div>
              <div class="AknVerticalList-delete action" title="Remove"></div>
          </li>
        <% }) %>
        <div class="AknMessageBox AknMessageBox--error AknMessageBox--hide alert alert-error ui-sortable-handle" style="">You must select at least one datagrid column to display</div>
    </ul>
  `;

  public events(): Backbone.EventsHash {
    return {
      'click [data-open]': 'openModal'
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

  loadAttributeGroups() {
    if (0 === this.loadedAttributeGroups.length) {
      return new Promise(resolve => {
        resolve({
          system: {
            label: 'System',
            children: 10,
          },
          marketing: {
            label: 'Marketing',
            children: 12,
          },
        });
      });
    }

    return new Promise(resolve => resolve(this.loadedAttributeGroups));
  }

  render() {
    this.$el.html(_.template(this.buttonTemplate));

    return this;
  }

  renderAttributeGroups() {
    this.loadAttributeGroups().then(attributeGroups => {
      this.modal.$el.find('[data-attributes]').empty()
        .append( _.template(this.attributeTemplate)({ groups: attributeGroups, }) );
    });
  }

  renderSelectedColumns() {
    const columns = [{code: 'image', label: 'Model picture', group: 'Media', groupOrder: 9}];
    const template = _.template(this.selectedTemplate)({ columns })
    this.modal.$el.find('[data-columns-selected]').empty().append(template);
  }

  renderColumns() {
    const columns = [{code: 'image', label: 'Model picture', group: 'Media', groupOrder: 9}];
    const template = _.template(this.columnsTemplate)({ columns })
    this.modal.$el.find('[data-columns]').empty().append(template);
  }

  openModal() {
    console.log('openModal')
    const modal = new Backbone.BootstrapModal({
      className: 'modal modal--fullPage modal--topButton column-configurator-modal',
      modalOptions: {
        backdrop: 'static',
        keyboard: false,
      },
      allowCancel: true,
      okCloses: false,
      cancelText: __('pim_common.cancel'),
      title: __('pim_datagrid.column_configurator.title'),
      content: _.template(this.modalTemplate)(),
      okText: __('pim_common.apply'),
    });

    modal.open();

    this.modal = modal;
    this.modal.$el.on('keyup', 'input[type="search"]', this.searchColumns.bind(this))

    this.renderAttributeGroups();
    this.renderSelectedColumns();
    this.renderColumns()
  }

  searchColumns(event: JQuery.Event) {
    console.log('searchColumns', event)
  }

  applyColumns() {

  }
}

export = ColumnSelector;
