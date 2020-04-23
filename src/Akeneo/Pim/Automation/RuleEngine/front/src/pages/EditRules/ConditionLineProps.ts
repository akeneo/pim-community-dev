import { Translate } from '../../dependenciesTools';
import { Locale } from '../../models';
import { IndexedScopes } from '../../fetch/ScopeFetcher';

type ConditionLineProps = {
  register: any;
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
};

export { ConditionLineProps };
