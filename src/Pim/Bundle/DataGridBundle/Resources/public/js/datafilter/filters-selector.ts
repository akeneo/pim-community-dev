import BaseView = require('pimenrich/js/view/base')

const mediator = require('oro/mediator')
const requireContext = require('require-context')

class FiltersColumn extends BaseView {
  public modules: any
  public datagridCollection: any

  public config = {
    filterTypes: {
        string: 'choice',
        choice: 'select',
        selectrow: 'select-row',
        multichoice: 'multiselect',
        boolean: 'select'
    }
  }

  constructor(options: {config: any}) {
    super(options)

    this.config = {...this.config, ...options.config}
    this.modules = {}
    this.datagridCollection = null;
  }

  configure() {
    this.listenTo(mediator, 'filters-column:updatedFilters', this.renderFilters)

    return BaseView.prototype.configure.apply(this, arguments)
  }

  getFilterModules(filters: any) {
    filters.forEach((filter: any) => {
      const types: any = this.config.filterTypes
      const filterType = types[filter.type] || filter.type

      if (!this.modules[filter.name]) {
        const filterModule = requireContext(`oro/datafilter/${filterType}-filter`)
        this.modules[filter.name] = new (filterModule.extend(filter))(filter);
      }
    })
  }

  renderFilters(filters: any, datagridCollection: any) {
    this.getFilterModules(filters)
    this.datagridCollection = datagridCollection

    const list = document.createDocumentFragment();

    for (let moduleName in this.modules) {
      const filterModule = this.modules[moduleName]
      filterModule.render()
      filterModule.on('update', this.updateDatagridStateWithFilters.bind(this))
      filterModule.on('disable', this.updateDatagridStateWithFilters.bind(this))
      list.appendChild(filterModule.el)
    }

    this.el.appendChild(list)

    mediator.trigger('datagrid_filters:rendered', datagridCollection, filters);
  }

  updateDatagridStateWithFilters() {
    const filterState: any = {}

    for (let filterName in this.modules) {
      const filter = this.modules[filterName]
      const shortName = `__${filterName}`

      if (filter.enabled) {
          if (!filter.isEmpty()) {
              filterState[filterName] = filter.getValue();
          } else if (!filter.defaultEnabled) {
              filterState[shortName] = 1;
          }
      } else if (filter.defaultEnabled) {
          filterState[shortName] = 0;
      }
    }

    this.datagridCollection.state.filters = filterState;
    this.datagridCollection.state.currentPage = 1;
    this.datagridCollection.fetch();
  }
}

export = FiltersColumn
