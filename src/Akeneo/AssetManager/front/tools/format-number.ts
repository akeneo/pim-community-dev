const UserContext = require('pim/user-context');

const STANDARD_DECIMAL_SEPARATOR = '.';

const unformatNumber = (numberToUnformat: string): string => {
  let result = numberToUnformat.replace(/\s*/g, '');

  if (STANDARD_DECIMAL_SEPARATOR !== decimalSeparator()) {
    result = result.replace(new RegExp(decimalSeparator(), 'g'), STANDARD_DECIMAL_SEPARATOR);
  }

  return result.replace(/[a-z]*[A-Z]*\s*/g, '');
};

const formatNumberForUILocale = (number: string): string =>
  number.replace(new RegExp('\\' + STANDARD_DECIMAL_SEPARATOR, 'g'), decimalSeparator());
const decimalSeparator = (): string => UserContext.get('ui_locale_decimal_separator');

export {unformatNumber, formatNumberForUILocale};
