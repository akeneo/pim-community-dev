export const redirectToRoute = (route: string, params: any) => {
  return {type: 'REDIRECT_TO_ROUTE', route, params}
}
