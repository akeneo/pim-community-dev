import React from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MultiSelectInput} from 'akeneo-design-system';
import {useFetchOptions} from '../../product';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SelectOptionCode} from '../../models';
import {useAttributeContext} from '../../contexts';

const MultiSelectFilterValue: TableFilterValueRenderer = ({value, onChange, columnCode}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const options = getOptionsFromColumnCode(columnCode);
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');

  const filteredOptions = (options || [])
    .filter(option => {
      return (
        option.code.toLowerCase().includes(searchValue.toLowerCase()) ||
        getLabel(option.labels, catalogLocale, option.code).toLowerCase().includes(searchValue.toLowerCase())
      );
    })
    .slice(0, (page + 1) * 20);

  return (
    <MultiSelectInput
      value={(value as string[] | undefined) || []}
      openLabel={translate('pim_common.open')}
      emptyResultLabel={translate('pim_common.no_result')}
      removeLabel={translate('pim_common.remove')}
      onChange={onChange}
      onSearchChange={setSearchValue}
      placeholder={translate('pim_table_attribute.datagrid.select_your_value')}
      onNextPage={() => setPage(page + 1)}
    >
      {filteredOptions.map(option => (
        <MultiSelectInput.Option value={option.code} key={option.code}>
          {getLabel(option.labels, catalogLocale, option.code)}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

const useValueRenderer: FilteredValueRenderer = (value, columnCode) => {
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const userContext = useUserContext();
  const column = attribute?.table_configuration.find(({code}) => code === columnCode);

  if (!columnCode || column?.data_type !== 'select') {
    return null;
  }

  const options = getOptionsFromColumnCode(columnCode) || [];
  const catalogLocale = userContext.get('catalogLocale');

  return ((value as string[] | undefined) || [])
    .map((subValue: SelectOptionCode) => {
      const option = options.find(option => option.code === subValue);
      return getLabel(option?.labels || {}, catalogLocale, option?.code || subValue);
    })
    .join(', ');
};

export {useValueRenderer};
export default MultiSelectFilterValue;
