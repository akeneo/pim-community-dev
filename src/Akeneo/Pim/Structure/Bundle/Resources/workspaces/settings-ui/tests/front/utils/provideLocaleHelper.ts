import {Locale} from '@akeneo-pim-community/settings-ui';

const aLocale = (code: string = 'xx_XX', label?: string, id: number = 123): Locale => {
  return {id, code, label: label || `the ${code} locale`};
};

const aListOfLocales = (codes: string[]): Locale[] => {
  return codes.map((code, index) => aLocale(code, undefined, index));
};

export {aLocale, aListOfLocales};
