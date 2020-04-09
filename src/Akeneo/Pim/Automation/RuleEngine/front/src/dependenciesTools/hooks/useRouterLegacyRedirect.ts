import { useApplicationContext } from "./useApplicationContext";
import { Router } from "../provider/applicationDependenciesProvider.type";

const buildRoute = (url: string, urlParams?: string) => {
  if (urlParams) {
    return `${url}/${urlParams}`;
  }
  return url;
};

const useLegacyRouter = () => {
  const { router } = useApplicationContext();
  if (router) {
    return router;
  }
  throw new Error(
    "[ApplicationContext]: Router has not been properly initiated"
  );
};

const generateUrl = (
  router: Router,
  route: string,
  urlParams?: string
): string => buildRoute(router.generate(route), urlParams);

const redirectToUrl = (router: Router, url: string): void =>
  router.redirect(url);

const generateAndRedirect = (
  router: Router,
  route: string,
  urlParams?: string
): [string, () => void] => {
  const url = generateUrl(router, route, urlParams);
  const handleRedirect = () => redirectToUrl(router, url);
  return [url, handleRedirect];
};

const useRouterLegacyRedirect = (
  route: string,
  urlParams?: string
): [string, () => void] => {
  const router = useLegacyRouter();
  if (router) {
    const resolvedURL = buildRoute(router.generate(route), urlParams);
    return [resolvedURL, () => router.redirect(resolvedURL)];
  }
  throw new Error(
    "[ApplicationContext]: Router has not been properly initiated"
  );
};

export {
  generateAndRedirect,
  generateUrl,
  redirectToUrl,
  useLegacyRouter,
  useRouterLegacyRedirect
};
