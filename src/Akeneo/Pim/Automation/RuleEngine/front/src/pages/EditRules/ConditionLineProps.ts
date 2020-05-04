import { Router, Translate } from '../../dependenciesTools';
import { Condition, Locale } from '../../models';
import { IndexedScopes } from '../../fetch/ScopeFetcher';

type ConditionLineProps = {
  condition: Condition;
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};

export { ConditionLineProps };
