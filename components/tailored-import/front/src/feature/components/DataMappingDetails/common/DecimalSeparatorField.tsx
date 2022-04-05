import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';

const availableDecimalSeparators = {
  '.': 'dot',
  ',': 'comma',
  '٫‎': 'arabic_comma',
};

type DecimalSeparator = keyof typeof availableDecimalSeparators;

const isDecimalSeparator = (separator: any): separator is DecimalSeparator => separator in availableDecimalSeparators;

type DecimalSeparatorProps = {
  value: DecimalSeparator;
  validationErrors: ValidationError[];
  onChange: (value: DecimalSeparator) => void;
};

const DecimalSeparatorField = ({value, validationErrors, onChange}: DecimalSeparatorProps) => {
  const translate = useTranslate();

  return (
    <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')}>
      <SelectInput
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={value}
        onChange={decimalSeparator => {
          if (isDecimalSeparator(decimalSeparator)) {
            onChange(decimalSeparator);
          }
        }}
      >
        {Object.entries(availableDecimalSeparators).map(([separator, name]) => (
          <SelectInput.Option
            key={separator}
            title={translate(`akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.${name}`)}
            value={separator}
          >
            {translate(`akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.${name}`)}
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

export {DecimalSeparatorField, isDecimalSeparator};
export type {DecimalSeparator};
