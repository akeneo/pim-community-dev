import { Router } from '../dependenciesTools';
import { httpGet } from './fetch';
import { Locale, Scope } from '../models';

type IndexedScopes = { [scopeCode: string]: Scope };

let cachedScopes: IndexedScopes;

const getAllScopes = async (router: Router): Promise<IndexedScopes> => {
  if (!cachedScopes) {
    cachedScopes = {};
    const url = router.generate('pim_enrich_channel_rest_index');
    const response = await httpGet(url);
    const json = await response.json();
    json.forEach((scope: Scope) => {
      cachedScopes[scope.code] = scope;
    });
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

  if (null === (await getScopeByCode(scopeCode, router))) {
    console.error(`The ${scopeCode} scope code does not exist`);

    return false;
  }

  return true;
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
    console.error(
      `The ${localeCode} locale code is not bound to the ${scopeCode} scope code`
    );

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
