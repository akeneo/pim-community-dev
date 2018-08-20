import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';

const mediator = require('oro/mediator')

interface FiltersConfig {
  title: string;
  description: string;
}

class FiltersColumn extends BaseView {
  readonly config: FiltersConfig;
  readonly template: string = `
    <button type="button" class="AknFilterBox-addFilterButton" aria-haspopup="true" style="width: 280px;">
        <div>Filters</div>
    </button>
    <div class="filter-selector">
        Enabled filters
    <div>
    <div
        class="ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-addFilterButton filter-list select-filter-widget pimmultiselect"
        style="width: 230px; display: block; top: -191px; left: 360px; position:fixed; overflow: scroll"
    >
        <div class="ui-multiselect-filter"><input placeholder="" type="search"></div>
        <div class="filters-column"></div>
    </div>
  `

  readonly filterListTemplate: string = `
    <ul class="ui-multiselect-checkboxes ui-helper-reset">
        <li class="ui-multiselect-optgroup-label">
            <a href="#"><%- groupName %></a>
        </li>
        <% filters.forEach(filter => { %>
            <label for="<%- filter.name %>" title="" class="ui-corner-all ui-state-hover">
                <input id="<%- filter.name %>" name="multiselect_add-filter-select" type="checkbox" value="<%- filter.name %>" title="<%- filter.label %>" <%- filter.enabled ? 'checked="checked"' : ''  %> aria-selected="true">
                    <span><%- filter.label %></span>
                </label>
        <% }) %>
    </ul>`

  constructor(options: {config: FiltersConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  fetchFilters() {
      return $.get('datagrid/product-grid/attributes-filters')
  }

  loadFilterList(gridCollection: any, gridElement: any) {
    console.log('gridCollection', gridCollection)
    console.log('loadFilterList', gridElement)
    const metadata = gridElement.data('metadata') || {};
    const defaultFilters = metadata.filters;

    this.fetchFilters().then(loadedFilters => {
        const mergedFilters = defaultFilters.concat(loadedFilters)
        const groupedFilters: any = this.groupFilters(mergedFilters)

        for (let groupName in groupedFilters) {
            const group = groupedFilters[groupName]
            this.renderFilterGroup(group, groupName)
        }
    })
  }

  renderFilterGroup(filters: any, groupName: string) {
      console.log('renderFilterGroup', filters, groupName)
      this.$('.filters-column').append(_.template(this.filterListTemplate)({ filters, groupName}))
  }

  groupFilters(filters: any) {
      return _.groupBy(filters, (filter: any) => filter.group || 'System')
  }

  configure() {
    this.listenTo(mediator, 'datagrid_collection_set_after', this.loadFilterList);

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
      console.log('render filters-column', this)
      this.$el.html(_.template(this.template))

      return this
  }
}

export = FiltersColumn;
