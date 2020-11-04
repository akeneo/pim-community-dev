import {useApplicationContext} from './useApplicationContext';
import {
  Router,
  RouteParams,
} from '../provider/applicationDependenciesProvider.type';

const useBackboneRouter = (): Router => {
  const {router} = useApplicationContext();
  if (router) {
    return router;
  }
  throw new Error(
    '[ApplicationContext]: Router has not been properly initiated'
  );
};

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

export {generateAndRedirect, generateUrl, redirectToUrl, useBackboneRouter};
