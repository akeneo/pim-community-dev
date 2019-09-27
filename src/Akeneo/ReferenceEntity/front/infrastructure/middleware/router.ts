const Routing = require('routing');
const DatagridState = require('pim/datagrid/state');

export default (router: any) => () => (next: any) => (action: any) => {
  if ('REDIRECT_TO_ROUTE' === action.type) {
    router.redirectToRoute(action.route, action.params);

    return;
  }
  if ('UPDATE_CURRENT_SIDEBAR_TAB' === action.type) {
    const route = router.match(window.location.hash);
    if (undefined !== route.params.tab) {
      history.replaceState(null, '', '#' + Routing.generate(route.name, {...route.params, tab: action.currentTab}));
    }
  }
  if ('REDIRECT_TO_PRODUCT_GRID' === action.type) {
    const filters = `f[${action.selectedAttribute}][value][]=${action.recordCode}&f[${action.selectedAttribute}][type]=in`;

    DatagridState.set('product-grid', {
      filters: filters,
    });

    router.redirectToRoute('pim_enrich_product_index');
  }

  return next(action);
};
