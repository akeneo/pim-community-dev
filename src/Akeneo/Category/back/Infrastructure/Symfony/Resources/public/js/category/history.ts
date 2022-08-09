const BaseView = require('pimui/js/view/base');
const Routing = require('routing');
const requireContext = require('require-context');
const datagridBuilder = require('oro/datagrid-builder');

class CategoryHistory extends BaseView {
  identifier: number;

  render(): any {
    this.$el.html('<div id="grid-category-history" data-type="datagrid"></div>');
    console.log('test');

    const urlParams = {
      alias: 'history-grid',
      'history-grid': {
        object_class: 'category',
        object_id: this.getFormData().categoryId,
        _pager: {
          _page: 1,
          _per_page: 25,
        },
        _sort_by: {
          loggedAt: 'DESC',
        },
      },
    };

    $.get(Routing.generate('pim_datagrid_load', urlParams)).done(function (response: any) {
      $('#grid-category-history').data({metadata: response.metadata, data: JSON.parse(response.data)});

      const resolvedModules: any = response.metadata.requireJSModules.map((module: any) => {
        return requireContext(module);
      });

      datagridBuilder(resolvedModules);
    });

    return this;
  }
}

export = CategoryHistory;
