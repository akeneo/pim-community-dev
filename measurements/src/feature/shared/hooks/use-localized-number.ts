import {unformatNumber, formatNumber} from '../../shared/tools/number';
import {useUserContext} from '@akeneo-pim-community/legacy';

const useLocalizedNumber = (): [(number: string) => string, (number: string) => string] => {
  const decimalSeparator = useUserContext().get('ui_locale_decimal_separator');

  return [formatNumber(decimalSeparator), unformatNumber(decimalSeparator)];
};

export {useLocalizedNumber};
