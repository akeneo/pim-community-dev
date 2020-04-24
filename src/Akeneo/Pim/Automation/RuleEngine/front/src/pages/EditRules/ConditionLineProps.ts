import { Translate } from '../../dependenciesTools';
import { Condition, Locale } from '../../models';
import { IndexedScopes } from '../../fetch/ScopeFetcher';

type ConditionLineProps = {
  register: any;
  condition: Condition;
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
};

export { ConditionLineProps };
