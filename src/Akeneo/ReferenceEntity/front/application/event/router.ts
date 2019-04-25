export const redirectToRoute = (
  route: string,
  params: any = {},
  options: {trigger: boolean; replace: boolean} = {trigger: false, replace: false}
) => {
  return {type: 'REDIRECT_TO_ROUTE', route, params, options};
};

export const redirectToProductGrid = (
  selectedAttribute: string,
  recordCode: string
) => {
  return {type: 'REDIRECT_TO_PRODUCT_GRID', selectedAttribute, recordCode};
};
