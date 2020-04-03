import { useApplicationContext } from "./useApplicationContext";

const useRouterLegacyRedirect = (route: string): [string, () => void] => {
  const { router } = useApplicationContext();
  if (router) {
    const resolvedURL = `#${router.generate(route)}`;
    return [resolvedURL, () => router.redirect(router.generate(route))];
  }
  throw new Error(
    "[ApplicationContext]: Router has not been properly initiated"
  );
};

export { useRouterLegacyRedirect };
