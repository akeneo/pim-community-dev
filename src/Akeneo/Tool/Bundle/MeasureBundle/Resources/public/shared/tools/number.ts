const STANDARD_DECIMAL_SEPARATOR = '.';

const unformatNumber = (decimalSeparator: string) => (numberToUnformat: string): string => {
  let result = numberToUnformat.replace(/\s*/g, '');

  if (STANDARD_DECIMAL_SEPARATOR !== decimalSeparator) {
    result = result.replace(new RegExp(decimalSeparator, 'g'), STANDARD_DECIMAL_SEPARATOR);
  }

  return result.replace(/[a-z]*[A-Z]*\s*/g, '');
};

const formatNumberForUILocale = (decimalSeparator: string) => (number: string): string =>
  Number(number)
    .toString()
    .replace(new RegExp('\\' + STANDARD_DECIMAL_SEPARATOR, 'g'), decimalSeparator);

export {unformatNumber, formatNumberForUILocale};
