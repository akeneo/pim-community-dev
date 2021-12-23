import {SelectInput} from 'akeneo-design-system';
import {ANY_OPTION_CODE} from './RowSelector';
import {getLabel, LabelCollection, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import React from 'react';
import {RecordCode, ReferenceEntityRecord, SelectOption, SelectOptionCode} from '../models';

type RowSelectorSelectInputProps = {
  value?: SelectOption | ReferenceEntityRecord | null;
  onChange: (option?: ReferenceEntityRecord | SelectOption | null) => void;
  onNextPage: () => void;
  options: {
    labels: LabelCollection;
    code: string;
  }[];
  setSearchValue: (value: string) => void;
};

const RowSelectorSelectInput: React.FC<RowSelectorSelectInputProps> = ({
  value,
  onChange,
  onNextPage,
  options,
  setSearchValue,
}) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');

  const handleChange = React.useCallback(
    (code?: RecordCode | SelectOptionCode | null) => {
      if (null === code) {
        onChange(undefined);
      } else if (ANY_OPTION_CODE === code) {
        onChange(null);
      } else {
        onChange(options?.find(option => option?.code === code));
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
      value={typeof value === 'undefined' ? null : value === null ? ANY_OPTION_CODE : value.code}
      openLabel={translate('pim_common.open')}
      onNextPage={onNextPage}
      onSearchChange={setSearchValue}
    >
      {options.map(option => {
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

export {RowSelectorSelectInput};
