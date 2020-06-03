import { Translate } from '../../../../dependenciesTools';
import { Locale, LocaleCode } from '../../../../models';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';

type ActionLineProps = {
  lineNumber: number;
  translate: Translate;
  handleDelete: () => void;
  currentCatalogLocale: LocaleCode;
  locales: Locale[];
  scopes: IndexedScopes;
};

export { ActionLineProps };
