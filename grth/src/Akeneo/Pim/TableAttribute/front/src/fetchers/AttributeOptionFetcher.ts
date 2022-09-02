import {AttributeCode, AttributeOption} from '../models';
import {Router} from '@akeneo-pim-community/shared';

const byAttributeCode: (router: Router, selectAttributeCode: AttributeCode) => Promise<AttributeOption[]> = async (
  router,
  selectAttributeCode
) => {
  const url = router.generate('pim_table_attribute_get_attribute_options', {selectAttributeCode});

  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  const json = await response.json();

  return json as AttributeOption[];
};

export const AttributeOptionFetcher = {
  byAttributeCode,
};
