import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {isMeasurementDecimalSeparator, MeasurementDecimalSeparator} from '../SourceDetails/Measurement/model';

type DecimalSeparatorDropdownProps = {
  label?: string;
  value: MeasurementDecimalSeparator;
  decimalSeparators: {[key: string]: string};
  validationErrors: ValidationError[];
  onChange: (updatedValue: MeasurementDecimalSeparator) => void;
};

const DecimalSeparatorDropdown = ({
  label,
  value,
  decimalSeparators,
  onChange,
  validationErrors,
}: DecimalSeparatorDropdownProps) => {
  const translate = useTranslate();

  return (
    <Field
      label={label ?? translate('akeneo.syndication.data_mapping_details.sources.selection.decimal_separator.title')}
    >
      <SelectInput
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={value}
        onChange={decimal_separator => {
          if (isMeasurementDecimalSeparator(decimal_separator)) {
            onChange(decimal_separator);
          }
        }}
      >
        {Object.entries(decimalSeparators).map(([separator, name]) => (
          <SelectInput.Option
            key={separator}
            title={translate(`akeneo.syndication.data_mapping_details.sources.selection.decimal_separator.${name}`)}
            value={separator}
          >
            {translate(`akeneo.syndication.data_mapping_details.sources.selection.decimal_separator.${name}`)}
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
