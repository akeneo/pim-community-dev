import { Locale } from './Locale';

type ScopeCode = string;

type Scope = {
  code: ScopeCode;
  currencies: string[];
  locales: Locale[];
  category_tree: string;
  conversion_units: string[];
  labels: { [locale: string]: string };
  meta: any;
};

export { Scope, ScopeCode };
