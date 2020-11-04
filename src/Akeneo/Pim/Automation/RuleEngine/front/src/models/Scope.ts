import {Locale} from './Locale';
import {CurrencyCode} from './Currency';

type ScopeCode = string;

type Scope = {
  code: ScopeCode;
  currencies: CurrencyCode[];
  locales: Locale[];
  category_tree: string;
  conversion_units: string[];
  labels: {[locale: string]: string};
  meta: any;
};

export {Scope, ScopeCode};
