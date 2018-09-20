import BaseView = require('pimenrich/js/view/base')
import * as _ from 'underscore'

const mediator = require('oro/mediator')

interface FiltersConfig {
  title: string
  description: string
}

interface GridFilter {
    group: string
    label: string
    name: string
    enabled: boolean
}

// @TODO restore enabled status after search
// @TODO restore selected state after updating filters (decoupled from filters-selector)

class FiltersColumn extends BaseView {
  public timer: any = null
  public defaultFilters: GridFilter[] = []
  public loadedFilters: GridFilter[] = []
  public gridCollection: any
  public page: number = 1
  public opened = false
  public filterList = {}

  readonly config: FiltersConfig
  readonly template: string = `
    <button type="button" class="AknFilterBox-addFilterButton" aria-haspopup="true" style="width: 280px" data-toggle>
        <div>Filters</div>
    </button>
    <div class="filter-selector"><div>
    <div class="ui-multiselect-menu ui-widget ui-widget-content ui-corner-all AknFilterBox-addFilterButton filter-list select-filter-widget pimmultiselect"
        style="width: 230px;display: none;top: -191px;left: 59px;position:absolute;overflow: scroll"
    >
        <div class="ui-multiselect-filter"><input placeholder="" type="search"></div>
        <div class="filters-column"></div>
        <div class="AknColumn-bottomButtonContainer"><div class="AknButton AknButton--apply close">Done</div></div>
    </div>
  `

  readonly filterGroupTemplate: string = `
    <ul class="ui-multiselect-checkboxes ui-helper-reset">
        <li class="ui-multiselect-optgroup-label">
            <a><%- groupName %></a>
        </li>
        <% filters.forEach(filter => { %>
        <li>
            <label for="<%- filter.name %>" title="" class="ui-corner-all ui-state-hover">
                <input id="<%- filter.name %>" name="multiselect_add-filter-select" type="checkbox" value="<%- filter.name %>" title="<%- filter.label %>" <%- filter.enabled ? 'checked="checked"' : ''  %> aria-selected="true">
                    <span><%- filter.label %></span>
            </label>
        </li>
        <% }) %>
    </ul>`

  constructor(options: {config: FiltersConfig}) {
    super(options)

    this.config = {...this.config, ...options.config}
    this.defaultFilters = []
    this.loadedFilters = []
    this.gridCollection = {}
  }

  public events(): Backbone.EventsHash {
    return {
      'click [data-toggle]': 'togglePanel'
    }
  }

  togglePanel() {
    this.opened = !this.opened

    if (this.opened) {
       $(this.filterList).css({ left: 360, display: 'block' })
       $('input[type="search"]', this.filterList).focus();
    } else {
       $(this.filterList).css({ left: 59, display: 'none' })
    }
  }

  toggleFilter(event: JQueryEventObject): void {
    const name = $(event.currentTarget).attr('id')
    const checked = $(event.currentTarget).is(':checked')
    const filter = this.loadedFilters.find(filter => filter.name === name)

    if (filter) {
        filter.enabled = checked
    }

    this.triggerFiltersUpdated()
  }

  fetchFilters(search?: string | null, page: number = this.page) {
      const url = 'datagrid/product-grid/attributes-filters'
      return $.get(search ? `${url}?search=${search}` : `${url}?page=${page}`)
  }

  mergeAddedFilters(originalFilters: GridFilter[], addedFilters: GridFilter[]) {
    const filters = [ ...originalFilters, ...addedFilters]
    const uniqueFilters: any[] = []

    filters.forEach(mergedFilter => {
        if (undefined === uniqueFilters.find((searchedFilter) => searchedFilter.name === mergedFilter.name)) {
            uniqueFilters.push(mergedFilter)
        }
    })

    return uniqueFilters;
  }

  fetchNextFilters(event: JQueryMouseEventObject) {
      const list: any = event.currentTarget
      const scrollPosition = Math.max(0, list.scrollTop - 15)
      const bottomPosition = (list.scrollHeight - list.offsetHeight)
      const isBottom = bottomPosition === scrollPosition

      if (isBottom) {
        this.page = this.page + 1

        this.fetchFilters(null, this.page).then(loadedFilters => {
            if (loadedFilters.length === 0) {
                return this.stopListeningToListScroll()
            }

            this.loadedFilters = this.mergeAddedFilters(this.loadedFilters, loadedFilters)
            return this.renderFilters()
        })
      }
  }

  searchFilters(event: JQueryEventObject) {
    if (null !== this.timer) {
        clearTimeout(this.timer)
    }

    if (13 === event.keyCode) {
        this.doSearch()
    } else {
        this.timer = setTimeout(this.doSearch.bind(this), 200)
    }
  }

  doSearch() {
      const searchValue: any = $(this.filterList).find('input[type="search"]').val()

      if (searchValue.length === 0) {
          return this.renderFilters()
      }

     return this.fetchFilters(searchValue, 1).then((loadedFilters: GridFilter[]) => {
        const filters: GridFilter[] = this.defaultFilters.concat(loadedFilters)

        this.loadedFilters = this.mergeAddedFilters(this.loadedFilters, filters)

        return this.renderFilters(filters.filter((filter: GridFilter) => {
            const label: string = filter.label.toLowerCase()
            const name: string = filter.name.toLowerCase()
            const search: string = searchValue.toLowerCase()

            return label.includes(search) || name.includes(search)
        }))
      })
  }

  listenToListScroll(): void {
    $(this.filterList).off('scroll').on('scroll', this.fetchNextFilters.bind(this))
  }

  stopListeningToListScroll(): void {
    $(this.filterList).off('scroll')
  }

  renderFilters(filters = this.loadedFilters) {
    const groupedFilters: any = this.groupFilters(filters)
    const list = document.createDocumentFragment();
    const filterColumn = $(this.filterList).find('.filters-column')

    filterColumn.empty()

    for (let groupName in groupedFilters) {
        const group: GridFilter[] = groupedFilters[groupName]
        const groupElement = this.renderFilterGroup(group, groupName)
        list.appendChild($(groupElement).get(0));
    }

    filterColumn.append(list)

    $('input[type="checkbox"]', filterColumn).off('change')
    $('input[type="checkbox"]', filterColumn).on('change', this.toggleFilter.bind(this))
  }

  loadFilterList(gridCollection: any, gridElement: any) {
    const metadata = gridElement.data('metadata') || {}

    this.defaultFilters = metadata.filters
    this.gridCollection = gridCollection
    this.fetchFilters().then((loadedFilters: GridFilter[]) => {
        this.loadedFilters = this.mergeAddedFilters(this.defaultFilters, loadedFilters)
        this.renderFilters()
        this.listenToListScroll()
        this.triggerFiltersUpdated()
    })
  }

  disableFilter(filterToDisable: any) {
    this.loadedFilters.forEach(filter => {
        if (filter.name === filterToDisable.name) {
            filter.enabled = false;
        }
    })

    this.renderFilters();
  }

  triggerFiltersUpdated() {
    mediator.trigger('filters-column:update-filters', this.loadedFilters, this.gridCollection)
  }

  getSelectedFilters() {
    return $(this.filterList).find('input[checked]').map(((_, el: HTMLElement) => $(el).attr('id'))).toArray()
  }

  renderFilterGroup(filters: GridFilter[], groupName: string) {
      return _.template(this.filterGroupTemplate)({ filters, groupName })
  }

  groupFilters(filters: GridFilter[]) {
      return _.groupBy(filters, (filter: GridFilter) => filter.group || 'System')
  }

  configure() {
    this.listenTo(mediator, 'datagrid_collection_set_after', this.loadFilterList)
    this.listenTo(mediator, 'filters-selector:disable-filter', this.disableFilter)

    return BaseView.prototype.configure.apply(this, arguments)
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
      $('.filter-list').remove();

      this.$el.html(_.template(this.template))
      this.filterList = $('.filter-list').appendTo($('body'))

      $('input[type="search"]', this.filterList).on('keyup', this.searchFilters.bind(this))
      $('.filter-list', this.filterList).on('scroll', this.searchFilters.bind(this))
      $('.close', this.filterList).on('click', this.togglePanel.bind(this))

      return this
  }
}

export = FiltersColumn
