import BaseView = require('pimenrich/js/view/base')
import * as _ from 'underscore'

const mediator = require('oro/mediator')
const requireContext = require('require-context')

class FiltersColumn extends BaseView {
  public modules: any
  public datagridCollection: any
  public silent: boolean

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
    super({...options, ...{ className: 'filter-box' }})

    this.config = {...this.config, ...options.config}
    this.modules = {}
    this.datagridCollection = null;
    this.silent = false;
  }

  configure() {
    this.listenTo(mediator, 'filters-column:update-filters', this.renderFilters)

    return BaseView.prototype.configure.apply(this, arguments)
  }

  getFilterModule(filter: any) {
    const types: any = this.config.filterTypes
    const filterType = types[filter.type] || filter.type
    let cachedFilter = this.modules[filter.name]

    if (!cachedFilter) {
      const filterModule = requireContext(`oro/datafilter/${filterType}-filter`)
      return this.modules[filter.name] = new (filterModule.extend(filter))(filter);
    }

    return cachedFilter
  }

  disableFilter(filter: any) {
      mediator.trigger('filters-selector:disable-filter', filter)
      this.updateDatagridStateWithFilters()
  }

  renderFilters(filters: any, datagridCollection: any) {
    this.datagridCollection = datagridCollection
    const list = document.createDocumentFragment();
    const state = datagridCollection.state.filters

    // filters need to be merged with their values
    console.log('renderFilters', filters, datagridCollection)

    filters.forEach((filter: any) => {
      const filterModule =  this.getFilterModule(filter)

      if (true === filter.enabled || state[filter.name]) {
        filterModule.render()
        filterModule.off()

        this.stopListening(filterModule, 'update')
        this.stopListening(filterModule, 'disable')

        this.listenTo(filterModule, 'update', this.updateDatagridStateWithFilters.bind(this))
        this.listenTo(filterModule, 'disable', this.disableFilter.bind(this, filter))

        list.appendChild(filterModule.el)
      }

      if (filterModule.isSearch) {
        this.getRoot().$('.search-zone').empty().append(filterModule.$el.get(0));
      }
    })

    this.el.appendChild(list)
    this.hideDisabledFilters(filters)
    this.restoreFilterState(state, filters)

    mediator.trigger('filters-column:init', this.updateDatagridStateWithFilters.bind(this))
  }

  hideDisabledFilters(filters: any) {
    filters.forEach((filter: any) => {
      const filterModule = this.modules[filter.name];
      (false === filter.enabled) ? filterModule.disable() : filterModule.enable()
    })
  }

  restoreFilterState(state: any, filters: any) {
    console.log('restoreFilterState')
    this.silent = true

    filters.forEach((filter: any) => {
      const filterName = filter.name
      const filterModule = this.modules[filterName]
      const filterState = state[filterName]

      if (filterState) {
        console.log('filterName setValue', filterName, state[filterName], filterModule)
        filterModule.setValue(state[filterName])
      }
    })

    this.silent = false
    console.log('finish restore filter state')
  }

  updateDatagridStateWithFilters(filterState: any = {}) {
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

    const currentState = _.omit(this.datagridCollection.state.filters, 'scope');
    const updatedState = _.omit(filterState, 'scope')
    const stateHasChanged = !_.isEqual(currentState, updatedState)
    const currentStateIsEmpty = _.isEmpty(currentState)

    console.log('is the state equal ? ', currentState, updatedState, _.isEqual(currentState, updatedState))
    console.log('should we update?', (stateHasChanged || currentStateIsEmpty));

    if ((stateHasChanged || currentStateIsEmpty) && false === this.silent) {
      this.datagridCollection.state.filters = filterState;
      this.datagridCollection.state.currentPage = 1;
      this.datagridCollection.fetch();
    }
  }
}

export = FiltersColumn
