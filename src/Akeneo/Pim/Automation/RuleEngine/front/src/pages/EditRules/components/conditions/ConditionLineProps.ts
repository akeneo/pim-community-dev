import {Locale, LocaleCode} from '../../../../models';
import {IndexedScopes} from '../../../../repositories/ScopeRepository';

export type ConditionLineProps = {
  lineNumber: number;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
};
