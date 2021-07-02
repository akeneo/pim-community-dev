import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../../models/TableConfiguration';

const getSelectOptions: () => SelectOption[] = () => {
  return [
    {code: 'salt', labels: {en_US: 'Salt', de_DE: 'Achtzergüntlich'}},
    {code: 'pepper', labels: {en_US: 'Pepper'}},
    {code: 'eggs', labels: {}},
  ] as SelectOption[];
};

const fetchSelectOptions = async (
  _router: Router,
  _attributeCode: string,
  _columnCode: ColumnCode
): Promise<SelectOption[]> => {
  return new Promise(resolve => resolve(getSelectOptions()));
};

export {fetchSelectOptions, getSelectOptions};
