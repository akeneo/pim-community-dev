import {Locale} from './Locale';

type Channel = {
  code: string;
  currencies: string[];
  locales: Locale[];
  category_tree: string;
  conversion_units: string[];
  labels: {[locale: string]: string};
  meta: {[key: string]: any};
};

export {Channel};
