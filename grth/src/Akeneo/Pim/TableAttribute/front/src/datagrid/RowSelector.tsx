import React from 'react';
import {getLabel, useUserContext, useTranslate} from '@akeneo-pim-community/shared';
import {SelectInput} from 'akeneo-design-system';
import {SelectOption, SelectOptionCode, TableAttribute} from '../models';
import {useFetchOptions} from '../product';

type RowSelectorProps = {
  attribute: TableAttribute;
  onChange: (option: SelectOption | undefined | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a SelectOption: the user has selected a row
   */
  value?: SelectOption | null;
};

const RowSelector: React.FC<RowSelectorProps> = ({attribute, onChange, value}) => {
  const ANY_OPTION_CODE = '[any option]';
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(attribute.table_configuration[0].code);
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const anyRowOption: SelectOption = {
    code: ANY_OPTION_CODE,
    labels: {
      [catalogLocale]: translate('pim_table_attribute.datagrid.any_row'),
    },
  };

  const handleChange = (selectOptionCode: SelectOptionCode | null) => {
    if (null === selectOptionCode) {
      onChange(undefined);
    } else if (ANY_OPTION_CODE === selectOptionCode) {
      onChange(null);
    } else {
      onChange((options || []).find(option => option.code === selectOptionCode));
    }
  };

  const filteredOptions = [anyRowOption]
    .concat(options || [])
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
      value={typeof value === 'undefined' ? null : value === null ? ANY_OPTION_CODE : value.code}
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
