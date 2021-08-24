import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../models/TableConfiguration';
import {fetchSelectOptions} from '../fetchers/SelectOptionsFetcher';

const selectOptionsCalls: {[key: string]: Promise<SelectOption[] | undefined>} = {};
const selectOptionsCache: {[key: string]: SelectOption[] | null} = {};

const clearCacheSelectOptions: () => void = () => {
  for (const key in selectOptionsCache) {
    delete selectOptionsCache[key];
  }
};

const getSelectOptions = async (
  router: Router,
  attributeCode: string,
  columnCode: ColumnCode
): Promise<SelectOption[] | null> => {
  const key = `${attributeCode}-${columnCode}`;
  if (!(key in selectOptionsCache)) {
    if (!(key in selectOptionsCalls)) {
      selectOptionsCalls[key] = fetchSelectOptions(router, attributeCode, columnCode);
    }
    selectOptionsCache[key] = (await selectOptionsCalls[key]) ?? null;
  }
  return selectOptionsCache[key];
};

const getSelectOption = async (
  router: Router,
  attributeCode: string,
  columnCode: ColumnCode,
  selectOptionCode: string
): Promise<SelectOption | null> => {
  const options = await getSelectOptions(router, attributeCode, columnCode);

  return options?.find(option => option.code === selectOptionCode) ?? null;
};

export {getSelectOptions, getSelectOption, clearCacheSelectOptions};
