import {
  Router,
  RouteParams,
} from '../provider/applicationDependenciesProvider.type';

const generateUrl = (
  router: Router,
  route: string,
  routeParams?: RouteParams
): string => router.generate(route, routeParams);

const redirectToUrl = (router: Router, url: string): void =>
  router.redirect(url);

const generateAndRedirect = (
  router: Router,
  route: string,
  routeParams?: RouteParams
): [string, () => void] => {
  const url = generateUrl(router, route, routeParams);
  const handleRedirect = (): void => redirectToUrl(router, url);
  return [url, handleRedirect];
};

export {generateUrl, generateAndRedirect, redirectToUrl};
