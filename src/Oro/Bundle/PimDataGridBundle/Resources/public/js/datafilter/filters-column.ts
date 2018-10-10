import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';

const mediator = require('oro/mediator');
const Routing = require('routing');

interface FiltersConfig {
  title: string;
  description: string;
}

interface GridFilter {
  group: string;
  label: string;
  name: string;
  enabled: boolean;
}

class FiltersColumn extends BaseView {
  public defaultFilters: GridFilter[] = [];
  public filterList: JQuery<HTMLElement>;
  public gridCollection: any;
  public ignoredFilters: string[];
  public loadedFilters: GridFilter[] = [];
  public loading: boolean;
  public opened = false;
  public page: number = 1;
  public timer: number;
  public searchSelector: string;

  readonly config: FiltersConfig;
  readonly template: string = `
    <button type="button" class="AknFilterBox-addFilterButton" aria-haspopup="true" style="width: 280px" data-toggle>
        <div>Filters</div>
    </button>
    <div class="filter-selector"><div>
    <div class="ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-addFilterButton AknFilterBox-column filter-list select-filter-widget pimmultiselect">
        <div class="ui-multiselect-filter"><input placeholder="" type="search"></div>
        <div class="AknLoadingMask loading-mask filter-loading" style="top: 50px"></div>
        <div class="filters-column"></div>
        <div class="AknColumn-bottomButtonContainer AknColumn-bottomButtonContainer--sticky"><div class="AknButton AknButton--apply close">Done</div></div>
    </div>
  `;

  readonly filterGroupTemplate: string = `
    <ul class="ui-multiselect-checkboxes ui-helper-reset full">
        <li class="ui-multiselect-optgroup-label">
            <a><%- groupName %></a>
        </li>
        <% filters.forEach(filter => { %>
          <li>
              <label for="<%- filter.name %>" title="" class="ui-corner-all ui-state-hover">
                  <input
                  id="<%- filter.name %>"
                  name="multiselect_add-filter-select"
                  type="checkbox" value="<%- filter.name %>"
                  title="<%- filter.label %>"
                  <%- filter.enabled ? 'checked="checked"' : ''  %>
                  <%- true === ignoredFilters.includes(filter.name) ? 'disabled="true"' : ''  %>
                    aria-selected="true">
                      <span><%- filter.label %></span>
              </label>
          </li>
        <% }) %>
    </ul>`;

  constructor(options: {config: FiltersConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
    this.defaultFilters = [];
    this.gridCollection = {};
    this.ignoredFilters = ['scope'];
    this.loadedFilters = [];
    this.searchSelector = 'input[type="search"]'
  }

  public events(): Backbone.EventsHash {
    return {
      'click [data-toggle]': 'togglePanel',
    };
  }

  togglePanel() {
    this.opened = !this.opened;
    let timer: any = null;

    if (this.opened) {
      $(this.filterList).show()

      timer = setTimeout(() => {
        $(this.filterList).addClass('AknFilterBox-column--expanded')
        $(this.searchSelector, this.filterList).focus();
        clearTimeout(timer);
      }, 100);
    } else {
      $(this.filterList).removeClass('AknFilterBox-column--expanded')
      timer = setTimeout(() => {
        $(this.filterList).hide();
        clearTimeout(timer);
      }, 200);
    }
  }

  toggleFilter(event: JQueryEventObject): void {
    const filterElement: JQuery<Element> = $(event.currentTarget);
    const name = filterElement.attr('id');
    const checked = filterElement.is(':checked');
    const filter = this.loadedFilters.find((filter: GridFilter) => filter.name === name);

    if (filter) {
      filter.enabled = checked;
    }

    this.triggerFiltersUpdated();
  }

  fetchFilters(search?: string | null, page: number = this.page) {
    const url = Routing.generate('pim_datagrid_productgrid_attributes_filters')
    return $.get(search ? `${url}?search=${search}` : `${url}?page=${page}`);
  }

  mergeAddedFilters(originalFilters: GridFilter[], addedFilters: GridFilter[]): GridFilter[] {
    const enabledFilters = Object.keys(this.gridCollection.state.filters)
    const filters = [...originalFilters, ...addedFilters];
    const uniqueFilters: GridFilter[] = [];

    filters.forEach(mergedFilter => {
      if (enabledFilters.includes(mergedFilter.name)) {
        mergedFilter.enabled = true;
      }

      if (undefined === uniqueFilters.find(searchedFilter => searchedFilter.name === mergedFilter.name)) {
        uniqueFilters.push(mergedFilter);
      }
    });

    return uniqueFilters;
  }

  fetchNextFilters(event: JQueryMouseEventObject): void {
    const list: any = event.currentTarget;
    const scrollPosition = Math.max(0, list.scrollTop);
    const bottomPosition = list.scrollHeight - list.offsetHeight;
    const isBottom = bottomPosition === scrollPosition;

    if (isBottom) {
      this.page = this.page + 1;

      this.fetchFilters(null, this.page).then(loadedFilters => {
        if (loadedFilters.length === 0) {
          return this.stopListeningToListScroll();
        }

        this.loadedFilters = this.mergeAddedFilters(this.loadedFilters, loadedFilters);

        this.renderFilters();
        this.hideLoading()
      });
    }
  }

  searchFilters(event: JQueryEventObject): void {
    if (null !== this.timer) {
      clearTimeout(this.timer);
    }

    if (27 === event.keyCode) {
      $(this.filterList)
        .find(this.searchSelector)
        .val('')
        .trigger('keyup');
      return this.togglePanel();
    }

    if (13 === event.keyCode) {
      this.doSearch();
    } else {
      this.timer = setTimeout(this.doSearch.bind(this), 200);
    }
  }

  doSearch() {
    this.showLoading();

    const searchValue: any = $(this.filterList)
      .find(this.searchSelector)
      .val();

    if (searchValue.length === 0) {
      return this.renderFilters();
    }

    return this.fetchFilters(searchValue, 1).then((loadedFilters: GridFilter[]) => {
      const filters: GridFilter[] = this.defaultFilters.concat(loadedFilters);
      this.loadedFilters = this.mergeAddedFilters(this.loadedFilters, filters);
      const searchedFilters = this.filterBySearchTerm(filters, searchValue);

      return this.renderFilters(searchedFilters);
    });
  }

  filterBySearchTerm(filters: GridFilter[], searchValue: string) {
    return filters.filter((filter: GridFilter) => {
      const label: string = filter.label.toLowerCase();
      const name: string = filter.name.toLowerCase();
      const search: string = searchValue.toLowerCase();

      return label.includes(search) || name.includes(search);
    })
  }

  listenToListScroll(): void {
    $(this.filterList)
      .off('scroll')
      .on('scroll', this.fetchNextFilters.bind(this));
  }

  stopListeningToListScroll(): void {
    $(this.filterList).off('scroll');
  }

  renderFilters(filters = this.loadedFilters): void {
    const groupedFilters: {[name: string]: GridFilter[]} = this.groupFilters(filters);
    const list = document.createDocumentFragment();
    const filterColumn = $(this.filterList).find('.filters-column');

    filterColumn.empty();

    for (let groupName in groupedFilters) {
      const group: GridFilter[] = groupedFilters[groupName];
      const groupElement = this.renderFilterGroup(group, groupName);
      list.appendChild($(groupElement).get(0));
    }

    filterColumn.append(list);

    const checkbox = $('input[type="checkbox"]', filterColumn);
    checkbox.off('change');
    checkbox.on('change', this.toggleFilter.bind(this));
    this.hideLoading();
  }

  loadFilterList(gridCollection: any, gridElement: JQuery<HTMLElement>): void {
    const metadata = gridElement.data('metadata') || {};

    this.defaultFilters = metadata.filters;
    this.gridCollection = gridCollection;
    this.showLoading();

    this.fetchFilters().then((loadedFilters: GridFilter[]) => {
      this.loadedFilters = this.mergeAddedFilters(this.defaultFilters, loadedFilters);
      this.renderFilters();
      this.listenToListScroll();
      this.triggerFiltersUpdated();
    });
  }

  disableFilter(filterToDisable: GridFilter): void {
    this.loadedFilters.forEach(filter => {
      if (filter.name === filterToDisable.name) {
        filter.enabled = false;
      }
    });

    this.renderFilters();
  }

  triggerFiltersUpdated() {
    mediator.trigger('filters-column:update-filters', this.loadedFilters, this.gridCollection);
  }

  renderFilterGroup(filters: GridFilter[], groupName: string): string {
    return _.template(this.filterGroupTemplate)({
      filters,
      groupName,
      ignoredFilters: this.ignoredFilters,
    });
  }

  groupFilters(filters: GridFilter[]) {
    return _.groupBy(filters, (filter: GridFilter) => filter.group || 'System');
  }

  configure() {
    this.listenTo(mediator, 'datagrid_collection_set_after', this.loadFilterList);
    this.listenTo(mediator, 'filters-selector:disable-filter', this.disableFilter);

    return BaseView.prototype.configure.apply(this, arguments);
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    $('.filter-list').remove();

    this.$el.html(_.template(this.template));
    this.filterList = $('.filter-list').appendTo($('body'));

    $(this.searchSelector, this.filterList).on('keyup search', this.searchFilters.bind(this));
    $('.filter-list', this.filterList).on('scroll', this.searchFilters.bind(this));
    $('.close', this.filterList).on('click', this.togglePanel.bind(this));

    this.hideLoading();

    return this;
  }

  shutdown() {
    $(this.filterList)
      .off()
      .remove();

    BaseView.prototype.shutdown.apply(this, arguments);
  }

  showLoading() {
    $('.filter-loading').show();
  }

  hideLoading() {
    $('.filter-loading').hide();
  }
}

export = FiltersColumn;
