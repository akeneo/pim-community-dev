import {SelectInput} from 'akeneo-design-system';
import {getLabel, LabelCollection, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import React from 'react';
import {RecordCode, SelectOptionCode} from '../models';

const ANY_OPTION_CODE = '[any option]';

type RowSelectorSelectInputOption = {
  code: string;
  labels: LabelCollection;
};

type RowSelectorSelectInputProps<T extends RowSelectorSelectInputOption> = {
  value?: RecordCode | SelectOptionCode | null;
  onChange: (option?: RecordCode | SelectOptionCode | null) => void;
  onNextPage: () => void;
  options: T[];
  setSearchValue: (value: string) => void;
};

const RowSelectorSelectInput = <T extends RowSelectorSelectInputOption>({
  value,
  onChange,
  onNextPage,
  options,
  setSearchValue,
}: React.PropsWithChildren<RowSelectorSelectInputProps<T>>) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');

  const handleChange = React.useCallback(
    (code?: RecordCode | SelectOptionCode | null) => {
      if (null === code) {
        onChange(undefined);
      } else if (ANY_OPTION_CODE === code) {
        onChange(null);
      } else {
        onChange(options.find(option => option.code === code)?.code);
      }
    },
    [onChange, options]
  );

  return (
    <SelectInput
      clearLabel={translate('pim_common.clear_value')}
      clearable
      emptyResultLabel={translate('pim_common.no_result')}
      onChange={handleChange}
      placeholder={translate('pim_table_attribute.datagrid.select_your_row')}
      value={typeof value === 'undefined' ? null : value === null ? ANY_OPTION_CODE : value}
      openLabel={translate('pim_common.open')}
      onNextPage={onNextPage}
      onSearchChange={setSearchValue}>
      <SelectInput.Option title={translate('pim_table_attribute.datagrid.any_row')} value={ANY_OPTION_CODE}>
        {translate('pim_table_attribute.datagrid.any_row')}
      </SelectInput.Option>
      {options.map(option => {
        const label = getLabel(option.labels, catalogLocale, option.code);
        return (
          <SelectInput.Option title={option.code} value={option.code} key={option.code}>
            {label}
          </SelectInput.Option>
        );
      })}
    </SelectInput>
  );
};

export {RowSelectorSelectInput};
