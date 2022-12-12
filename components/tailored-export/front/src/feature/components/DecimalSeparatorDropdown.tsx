import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {availableDecimalSeparators, DecimalSeparator, isDecimalSeparator} from '../models';

type DecimalSeparatorDropdownProps = {
  label?: string;
  value: DecimalSeparator;
  validationErrors: ValidationError[];
  onChange: (updatedValue: DecimalSeparator) => void;
};

const DecimalSeparatorDropdown = ({label, value, onChange, validationErrors}: DecimalSeparatorDropdownProps) => {
  const translate = useTranslate();

  return (
    <Field
      label={label ?? translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
    >
      <SelectInput
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={value}
        onChange={decimal_separator => {
          if (isDecimalSeparator(decimal_separator)) {
            onChange(decimal_separator);
          }
        }}
      >
        {Object.entries(availableDecimalSeparators).map(([separator, name]) => (
          <SelectInput.Option
            key={separator}
            title={translate(`akeneo.tailored_export.column_details.sources.selection.decimal_separator.${name}`)}
            value={separator}
          >
            {translate(`akeneo.tailored_export.column_details.sources.selection.decimal_separator.${name}`)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {DecimalSeparatorDropdown};
