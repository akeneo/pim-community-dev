import {IndexedScopes} from '../../../src/repositories/ScopeRepository';
import {Locale, Scope} from '../../../src/models';

export const createLocale = (data: {[key: string]: any}): Locale => {
  return {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
    ...data,
  };
};

export const locales: Locale[] = [
  {
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  },
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];

export const uiLocales: Locale[] = [
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'es_ES',
    label: 'Spanish (Spain)',
    region: 'Spain',
    language: 'Spanish',
  },
];

export const createScope = (data: {[key: string]: any}): Scope => {
  return {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales: locales,
    category_tree: 'master',
    conversion_units: [],
    labels: {en_US: 'e-commerce'},
    meta: {},
    ...data,
  };
};

export const scopes: IndexedScopes = {
  ecommerce: {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales: locales,
    category_tree: 'master',
    conversion_units: [],
    labels: {en_US: 'e-commerce'},
    meta: {},
  },
  mobile: {
    code: 'mobile',
    currencies: ['EUR', 'USD'],
    locales: [locales[0], locales[1]],
    category_tree: 'master',
    conversion_units: [],
    labels: {en_US: 'Mobile'},
    meta: {},
  },
  print: {
    code: 'print',
    currencies: ['EUR'],
    locales: [locales[0], locales[1]],
    category_tree: 'master',
    conversion_units: [],
    labels: {en_US: 'Mobile'},
    meta: {},
  },
};
