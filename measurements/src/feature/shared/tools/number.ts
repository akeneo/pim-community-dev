const STANDARD_DECIMAL_SEPARATOR = '.';

const unformatNumber = (decimalSeparator: string) => (numberToUnformat: string): string => {
  decimalSeparator = decimalSeparator || STANDARD_DECIMAL_SEPARATOR;

  let result = numberToUnformat.replace(/\s*/g, '');

  if (STANDARD_DECIMAL_SEPARATOR !== decimalSeparator) {
    result = result.replace(new RegExp(decimalSeparator, 'g'), STANDARD_DECIMAL_SEPARATOR);
  }

  return result.replace(/[a-z]*[A-Z]*\s*/g, '');
};

const formatNumber = (decimalSeparator: string) => (number: string): string =>
  number.replace(new RegExp('\\' + STANDARD_DECIMAL_SEPARATOR, 'g'), decimalSeparator || STANDARD_DECIMAL_SEPARATOR);

export {unformatNumber, formatNumber};
