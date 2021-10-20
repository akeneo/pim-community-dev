import {Router} from '@akeneo-pim-community/shared';
import {TableAttribute, AttributeCode, ColumnCode, SelectOption, SelectColumnDefinition} from '../models';
import {SelectOptionFetcher} from '../fetchers';

const selectOptionsCalls: {[key: string]: Promise<SelectOption[] | undefined>} = {};
const selectOptionsCache: {[key: string]: SelectOption[] | null} = {};

const clearCacheSelectOptions: () => void = () => {
  for (const key in selectOptionsCalls) {
    delete selectOptionsCalls[key];
  }

  for (const key in selectOptionsCache) {
    delete selectOptionsCache[key];
  }
};

const getSelectOptions = async (
  router: Router,
  attributeCode: AttributeCode,
  columnCode: ColumnCode
): Promise<SelectOption[] | null> => {
  const key = `${attributeCode}-${columnCode}`;
  if (!(key in selectOptionsCache)) {
    if (!(key in selectOptionsCalls)) {
      selectOptionsCalls[key] = SelectOptionFetcher.fetchFromColumn(router, attributeCode, columnCode);
    }
    selectOptionsCache[key] = (await selectOptionsCalls[key]) ?? null;
  }
  return selectOptionsCache[key];
};

const getSelectOption = async (
  router: Router,
  attributeCode: AttributeCode,
  columnCode: ColumnCode,
  selectOptionCode: string
): Promise<SelectOption | null> => {
  const options = await getSelectOptions(router, attributeCode, columnCode);

  return options?.find(option => option.code.toLowerCase() === selectOptionCode.toLowerCase()) ?? null;
};

const saveSelectOptions = async (
  router: Router,
  attribute: TableAttribute,
  columnCode: ColumnCode,
  options: SelectOption[]
): Promise<boolean> => {
  const url = router.generate('pim_enrich_attribute_rest_post', {
    identifier: attribute.code
  });

  const table_configuration = attribute.table_configuration;
  const i = table_configuration.findIndex(column => column.code === columnCode);
  table_configuration[i] = {...(table_configuration[i] as SelectColumnDefinition), options}
  const body = {table_configuration};

  return fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(body)
  }).then(() => {
    clearCacheSelectOptions();
    return true;
  }).catch(error => {
    console.error(error);
    return false;
  });
}

const SelectOptionRepository = {
  findFromColumn: getSelectOptions,
  findFromCell: getSelectOption,
  clearCache: clearCacheSelectOptions,
  save: saveSelectOptions,
};

export {SelectOptionRepository};
