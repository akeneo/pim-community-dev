import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../models/TableConfiguration';

const fetchSelectOptions = async (
  router: Router,
  attributeCode: string,
  columnCode: ColumnCode
): Promise<SelectOption[]> => {
  const url = router.generate('pim_table_attribute_get_select_options', {
    attributeCode,
    columnCode,
  });
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  return await response.json();
};

export {fetchSelectOptions};
