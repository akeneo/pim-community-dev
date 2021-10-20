import React from 'react';
import {getLabel, useUserContext, useTranslate} from '@akeneo-pim-community/shared';
import {SelectInput} from 'akeneo-design-system';
import {SelectOption, SelectOptionCode, TableAttribute} from '../models';
import {useFetchOptions} from '../product';

type RowSelectorProps = {
  attribute: TableAttribute;
  onChange: (option: SelectOption | undefined) => void;
  value?: SelectOption;
};

const RowSelector: React.FC<RowSelectorProps> = ({attribute, onChange, value}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(attribute.table_configuration[0].code);
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');

  const handleChange = (selectOptionCode: SelectOptionCode | null) => {
    onChange((options || []).find(option => option.code === selectOptionCode));
  };

  const filteredOptions = (options || [])
    .filter(option => {
      return (
        option.code.toLowerCase().includes(searchValue.toLowerCase()) ||
        getLabel(option.labels, catalogLocale, option.code).toLowerCase().includes(searchValue.toLowerCase())
      );
    })
    .slice(0, (page + 1) * 20);

  return (
    <SelectInput
      clearLabel={translate('pim_common.clear_value')}
      clearable
      emptyResultLabel={translate('pim_common.no_result')}
      onChange={handleChange}
      placeholder={translate('pim_table_attribute.datagrid.select_your_row')}
      value={value?.code || null}
      openLabel={translate('pim_common.open')}
      onNextPage={() => {
        setPage(page + 1);
      }}
      onSearchChange={setSearchValue}
    >
      {filteredOptions.map(option => {
        const label = getLabel(option.labels, catalogLocale, option.code);
        return (
          <SelectInput.Option title={label} value={option.code} key={option.code}>
            {label}
          </SelectInput.Option>
        );
      })}
    </SelectInput>
  );
};

export {RowSelector};
