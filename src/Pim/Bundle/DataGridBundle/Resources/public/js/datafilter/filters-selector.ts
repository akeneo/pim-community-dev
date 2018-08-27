import BaseView = require('pimenrich/js/view/base')

const mediator = require('oro/mediator')
const requireContext = require('require-context')

class FiltersColumn extends BaseView {
  public modules: any

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
  }

  configure() {
    console.log('configure filters-selector')
    this.listenTo(mediator, 'filters-column:updatedFilters', this.renderFilters)

    return BaseView.prototype.configure.apply(this, arguments)
  }

  getFilterModules(filters: any) {
    filters.forEach((filter: any) => {
      const types: any = this.config.filterTypes
      const filterType = types[filter.type] || filter.type

      if (!this.modules[filter.name]) {
        console.log('module doesnt exist, load it')
        const filterModule = requireContext(`oro/datafilter/${filterType}-filter`)
        this.modules[filter.name] = new (filterModule.extend(filter))(filter);
      }
    })
  }

  // Add filters incrementally
  renderFilters(filters: any) {
    this.getFilterModules(filters)
    console.log(this.modules)

    const list = document.createDocumentFragment();

    for (let moduleName in this.modules) {
      const filterModule = this.modules[moduleName]
      filterModule.render()
      list.appendChild(filterModule.el)
    }

    this.el.appendChild(list)
  }
}

export = FiltersColumn
