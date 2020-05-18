import { Router, Translate } from '../../../../dependenciesTools';
import { Locale } from '../../../../models';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';

export type ConditionLineProps = {
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};
