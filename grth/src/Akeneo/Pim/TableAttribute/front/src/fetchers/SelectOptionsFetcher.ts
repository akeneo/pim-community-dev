import {Router} from '@akeneo-pim-community/shared';
import {AttributeCode, ColumnCode, SelectOption} from '../models';

const fetchSelectOptions = async (
  router: Router,
  attributeCode: AttributeCode,
  columnCode: ColumnCode
): Promise<SelectOption[] | undefined> => {
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

  if (response.status === 404) {
    return undefined;
  }

  return await response.json();
};

const SelectOptionFetcher = {
  fetchFromColumn: fetchSelectOptions,
};

export {SelectOptionFetcher};
