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
  columnCode: ColumnCode
): Promise<SelectOption[] | undefined> => {
  if (columnCode === 'ingredient') {
    return new Promise(resolve => resolve(getSelectOptions()));
  } else {
    return new Promise(resolve => resolve(undefined));
  }
};

export {fetchSelectOptions, getSelectOptions};
