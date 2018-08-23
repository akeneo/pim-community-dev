import BaseView = require('pimenrich/js/view/base')

const mediator = require('oro/mediator')

class FiltersColumn extends BaseView {
  configure() {
    this.listenTo(mediator, 'filters-column:updatedFilters', this.renderFilters)

    return BaseView.prototype.configure.apply(this, arguments)
  }

  renderFilters(filters) {
      console.log('renderFilters', filters)
  }

  render(): BaseView {
    console.log('filters-selector')

    return this
  }
}

export = FiltersColumn
