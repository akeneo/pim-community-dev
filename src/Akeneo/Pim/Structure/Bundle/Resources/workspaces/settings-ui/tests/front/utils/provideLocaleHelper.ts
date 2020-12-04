import {Locale} from '../../../src/models';

const aLocale = (code: string = 'xx_XX', label?: string): Locale => {
  return {code, label: label || `the ${code} locale`};
};

const aListOfLocales = (codes: string[]): Locale[] => {
  return codes.map(code => aLocale(code));
};

export {aLocale, aListOfLocales};
