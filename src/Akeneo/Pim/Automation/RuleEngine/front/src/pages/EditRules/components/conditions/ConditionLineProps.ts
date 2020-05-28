import { Router, Translate } from '../../../../dependenciesTools';
import { Locale, LocaleCode } from '../../../../models';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';

export type ConditionLineProps = {
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  router: Router;
};
