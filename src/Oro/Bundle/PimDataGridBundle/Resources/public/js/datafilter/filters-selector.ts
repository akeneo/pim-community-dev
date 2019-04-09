import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';

const mediator = require('oro/mediator');
const requireContext = require('require-context');

interface FilterModule extends Backbone.View<any> {
  enabled: boolean;
  defaultEnabled: boolean;
  isSearch?: boolean;
  enable: () => FilterModule;
  disable: () => FilterModule;
  isEmpty: () => boolean;
  getValue: () => FilterValue;
  reset: () => FilterModule;
  setValue: (value: FilterValue | number) => FilterModule;
  extend: (filterDefinition: FilterDefinition) => any;
  moveFilter?: (collection: any, element: any) => void;
}

interface FilterDefinition {
  name: string;
  type: string;
  populateDefault: boolean;
  enabled: boolean;
}

interface FilterValue {
  type: string;
  value: any;
}

interface FilterState {
  [name: string]: FilterValue | number;
}

class FiltersSelector extends BaseView {
  public modules: {[name: string]: FilterModule};
  public datagridCollection: any;
  public silent: boolean;
  public categoryFilter: any;

  public config = {
    filterTypes: {
      string: 'choice',
      choice: 'select',
      selectrow: 'select-row',
      multichoice: 'multiselect',
      boolean: 'select',
    },
  };

  constructor(options: {config: any}) {
    super({...options, ...{className: 'filter-box'}});

    this.config = {...this.config, ...options.config};
    this.modules = {};
    this.datagridCollection = null;
    this.silent = false;
    this.categoryFilter = {};
  }

  configure() {
    this.listenTo(mediator, 'filters-column:update-filters', this.renderFilters);
    this.listenTo(mediator, 'filters-column:update-filter', (categoryFilter: any, silent = false) => {
      this.categoryFilter = categoryFilter;

      if (false === silent) {
        this.updateGridState();
      }
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  getFilterModule(filter: FilterDefinition): FilterModule {
    const types: any = this.config.filterTypes;
    const filterType = types[filter.type] || filter.type;
    let cachedFilter: FilterModule = this.modules[filter.name];

    if (!cachedFilter) {
      const filterModule: FilterModule = requireContext(`oro/datafilter/${filterType}-filter`);

      if (!filterModule) {
        throw Error(`No module found for the ${filter.name} filter`);
      }

      return (this.modules[filter.name] = new (filterModule.extend(filter))(filter));
    }

    return cachedFilter;
  }

  disableFilter(filter: any): void {
    mediator.trigger('filters-selector:disable-filter', filter);

    this.updateGridState();
  }

  renderFilters(filters: FilterDefinition[], datagridCollection: any): void {
    this.datagridCollection = datagridCollection;
    const list: DocumentFragment = document.createDocumentFragment();
    const state: FilterState = datagridCollection.state.filters;

    filters.forEach((filter: FilterDefinition) => {
      const filterModule: FilterModule = this.getFilterModule(filter);

      if (true === filter.enabled || state[filter.name]) {
        filterModule.render();
        filterModule.off();

        this.stopListening(filterModule, 'update');
        this.stopListening(filterModule, 'disable');

        this.listenTo(filterModule, 'update', this.updateGridState.bind(this));
        this.listenTo(filterModule, 'disable', this.disableFilter.bind(this, filter));

        list.appendChild(filterModule.el);
      }

      if (undefined !== filterModule.moveFilter) {
        filterModule.moveFilter(datagridCollection, this.getRoot());
      }
    });

    this.el.appendChild(list);
    this.restoreFilterState(state, filters);

    mediator.trigger('filters-column:init', this.updateGridState.bind(this));
    mediator.trigger('datagrid_filters:rendered', datagridCollection);
  }

  restoreFilterState(state: FilterState, filters: FilterDefinition[]): void {
    this.silent = true;

    filters.forEach((filter: FilterDefinition) => {
      const filterName = filter.name;
      const filterModule: FilterModule = this.modules[filterName];
      const filterValue = state[filterName];

      if (false === filter.enabled) {
        filterModule.disable();
      } else {
        filterModule.enabled = false;
        filterModule.enable();
      }

      if (filterValue) {
        try {
          filterModule.reset()
          filterModule.setValue(filterValue);
          filterModule.enabled = true;
        } catch (e) {
          console.error('cant restore filter state for', filterName);
        }
      }
    });

    this.silent = false;
  }

  getState(): FilterState {
    let filterState: FilterState = {};

    for (let filterName in this.modules) {
      const filter = this.modules[filterName];
      const shortName = `__${filterName}`;

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

    return filterState;
  }

  updateGridState(): void {
    const categoryFilter: FilterState = {...this.categoryFilter};
    const currentState: FilterState = this.datagridCollection.state.filters;
    const updatedState: FilterState = Object.assign(this.getState(), categoryFilter);

    const stateHasChanged = !_.isEqual(currentState, updatedState);
    const currentStateIsEmpty = _.isEmpty(currentState);
    const shouldReloadState = (stateHasChanged || currentStateIsEmpty) && false === this.silent;

    if (shouldReloadState) {
      this.datagridCollection.state.filters = updatedState;
      this.datagridCollection.state.currentPage = 1;
      this.datagridCollection.fetch();
    }
  }
}

export = FiltersSelector;
