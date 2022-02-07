import {AttributeCode, AttributeOption} from '../../models';
import {Router} from '@akeneo-pim-community/shared';

const byAttributeCode: (router: Router, selectAttributeCode: AttributeCode) => Promise<AttributeOption[]> = async (
  _router,
  _selectAttributeCode
) => {
  return new Promise(resolve =>
    resolve([
      {
        code: 'option_1',
        labels: {en_US: 'Option 1 English'},
      },
      {
        code: 'simple_select_option_2',
        labels: {fr_FR: 'Option 2 French'},
      },
    ])
  );
};

export const AttributeOptionFetcher = {
  byAttributeCode,
};
