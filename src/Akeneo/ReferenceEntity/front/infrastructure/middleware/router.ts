const Routing = require('routing');

export default (router: any) => () => (next: any) => (action: any) => {
  if ('REDIRECT_TO_ROUTE' === action.type) {
    router.redirectToRoute(action.route, action.params);

    return;
  }
  if ('UPDATE_CURRENT_SIDEBAR_TAB' === action.type) {
    const route = router.match(window.location.hash);
    if (undefined !== route.params.tab) {
      history.replaceState(
        null,
        '',
        '#' + Routing.generate(route.name, {...route.params, tab: action.currentTab})
      );
    }
  }

  return next(action);
};
