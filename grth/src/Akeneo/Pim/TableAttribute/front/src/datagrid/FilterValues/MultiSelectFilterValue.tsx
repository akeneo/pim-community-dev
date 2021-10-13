import React, {useEffect} from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MultiSelectInput} from 'akeneo-design-system';
import {useFetchOptions} from '../../product';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SelectOptionCode, TableAttribute} from '../../models';
import {AttributeFetcher} from '../../fetchers';

const StringFilterValue: TableFilterValueRenderer = ({value, onChange, attribute, columnCode}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(columnCode);
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');

  const filteredOptions = (options || [])
    .filter(option => {
      return (
        option.code.toLowerCase().includes(searchValue.toLowerCase()) ||
        getLabel(option.labels, catalogLocale, option.code)
          .toLowerCase()
          .includes(searchValue.toLowerCase())
      );
    })
    .slice(0, (page + 1) * 20);

  return (
    <MultiSelectInput
      value={value || []}
      openLabel={translate('pim_common.open')}
      emptyResultLabel={translate('pim_common.no_result')}
      removeLabel={translate('pim_common.remove')}
      onChange={onChange}
      onSearchChange={setSearchValue}
      onNextPage={() => setPage(page + 1)}>
      {filteredOptions.map(option => (
        <MultiSelectInput.Option value={option.code} key={option.code}>
          {getLabel(option.labels, catalogLocale, option.code)}
        </MultiSelectInput.Option>
      ))}
    </MultiSelectInput>
  );
};

const useValueRenderer: FilteredValueRenderer = attributeCode => {
  const [attribute, setAttribute] = React.useState<TableAttribute | undefined>();
  const userContext = useUserContext();
  const router = useRouter();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      setAttribute(attribute as TableAttribute);
    });
  }, []);

  const {getOptionsFromColumnCode} = useFetchOptions(attribute?.table_configuration, attributeCode, []);

  return (value, columnCode) => {
    const options = getOptionsFromColumnCode(columnCode) || [];
    const catalogLocale = userContext.get('catalogLocale');

    return value
      .map((subValue: SelectOptionCode) => {
        const option = options.find(option => option.code === subValue);
        return getLabel(option?.labels || {}, catalogLocale, option?.code || subValue);
      })
      .join(', ');
  };
};

export {useValueRenderer};
export default StringFilterValue;
