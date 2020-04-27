import { Locale } from './Locale';

type Scope = {
  code: string;
  currencies: string[];
  locales: Locale[];
  category_tree: string;
  conversion_units: string[];
  labels: { [locale: string]: string };
  meta: any;
};

export { Scope };
