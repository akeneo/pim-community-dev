import {Router} from "../dependenciesTools";
import {httpGet} from "./fetch";
import {Scope} from "../models";

type IndexedScopes = {[scopeCode: string]: Scope};

let cachedScopes: IndexedScopes;

const getScopes = async (router: Router): Promise<IndexedScopes> => {
  if (!cachedScopes) {
    cachedScopes = {};
    const url = router.generate('pim_enrich_channel_rest_index');
    const response = await httpGet(url);
    const json = await response.json();
    json.forEach((scope: Scope) => {
      cachedScopes[scope.code] = scope;
    })
  }

  return cachedScopes;
};

const getScopeByCode = async (scopeCode: string, router: Router): Promise<Scope | null> => {
  const scopes = await getScopes(router);

  return scopes[scopeCode] || null;
};

export { getScopes, getScopeByCode, IndexedScopes }
