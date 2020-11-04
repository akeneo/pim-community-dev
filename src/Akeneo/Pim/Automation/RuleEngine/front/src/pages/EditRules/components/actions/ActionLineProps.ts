import {Locale, LocaleCode} from '../../../../models';
import {IndexedScopes} from '../../../../repositories/ScopeRepository';

type ActionLineProps = {
  lineNumber: number;
  handleDelete: () => void;
  currentCatalogLocale: LocaleCode;
  locales: Locale[];
  scopes: IndexedScopes;
};

export {ActionLineProps};
