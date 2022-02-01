import {Router} from '@akeneo-pim-community/shared';
import {AttributeCode, castSelectColumnDefinition, ColumnCode, SelectOption, TableAttribute} from '../models';
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

const saveSelectOptions = async (
  router: Router,
  attribute: TableAttribute,
  columnCode: ColumnCode,
  options: SelectOption[]
): Promise<boolean> => {
  const url = router.generate('pim_enrich_attribute_rest_post', {
    identifier: attribute.code,
  });

  const table_configuration = attribute.table_configuration;
  const i = table_configuration.findIndex(column => column.code === columnCode);
  table_configuration[i] = {...castSelectColumnDefinition(table_configuration[i]), options};
  const body = {table_configuration};

  return fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(body),
  })
    .then(() => {
      clearCacheSelectOptions();
      return true;
    })
    .catch(error => {
      console.error(error);
      return false;
    });
};

const SelectOptionRepository = {
  findFromColumn: getSelectOptions,
  clearCache: clearCacheSelectOptions,
  save: saveSelectOptions,
};

export {SelectOptionRepository};
