import {Router} from '../dependenciesTools';
import {Locale, Scope} from '../models';
import {fetchAllScopes} from '../fetch/ScopeFetcher';

type IndexedScopes = {[scopeCode: string]: Scope};

let cachedScopes: IndexedScopes;

const getAllScopes = async (router: Router): Promise<IndexedScopes> => {
  if (!cachedScopes) {
    cachedScopes = (await fetchAllScopes(router)).reduce(
      (previousValue: IndexedScopes, currentValue) => {
        previousValue[currentValue.code] = currentValue;
        return previousValue;
      },
      {}
    );
  }

  return cachedScopes;
};

const getScopeByCode = async (
  scopeCode: string,
  router: Router
): Promise<Scope | null> => {
  const scopes = await getAllScopes(router);

  return scopes[scopeCode] || null;
};

const checkScopeExists = async (
  scopeCode: string | null,
  router: Router
): Promise<boolean> => {
  if (!scopeCode) {
    return true;
  }

  return null !== (await getScopeByCode(scopeCode, router));
};

const checkLocaleIsBoundToScope = async (
  localeCode: string | null,
  scopeCode: string | null,
  router: Router
): Promise<boolean> => {
  if (!scopeCode || !localeCode) {
    return true;
  }

  const scope: Scope | null = await getScopeByCode(scopeCode, router);
  if (null === scope) {
    return true;
  }

  if (!scope.locales.find((locale: Locale) => locale.code === localeCode)) {
    return false;
  }

  return true;
};

export {
  getAllScopes,
  getScopeByCode,
  IndexedScopes,
  checkScopeExists,
  checkLocaleIsBoundToScope,
};
