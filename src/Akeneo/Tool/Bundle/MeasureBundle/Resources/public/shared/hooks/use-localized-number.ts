import {UserContext} from 'akeneomeasure/context/user-context';
import {useContext} from 'react';
import {unformatNumber, formatNumber} from 'akeneomeasure/shared/tools/number';

const useLocalizedNumber = (): [(number: string) => string, (number: string) => string] => {
  const decimalSeparator = useContext(UserContext)('ui_locale_decimal_separator');

  return [formatNumber(decimalSeparator), unformatNumber(decimalSeparator)];
};

export {useLocalizedNumber};
