export default (router: any) => (store: any) => (next: any) => (action: any) => {
  if ('REDIRECT_TO_ROUTE' === action.type) {
    router.redirectToRoute(
        action.route,
        action.params ? action.params : {},
        {trigger: true}
    );

    return;
  }

  let result = next(action);

  return result;
}
