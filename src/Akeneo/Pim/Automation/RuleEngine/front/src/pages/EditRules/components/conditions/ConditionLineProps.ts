import { Router, Translate } from '../../../../dependenciesTools';
import { Locale } from '../../../../models';
import { IndexedScopes } from '../../../../fetch/ScopeFetcher';

export type ConditionLineProps = {
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};
