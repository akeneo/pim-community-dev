import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {availableDecimalSeparators, isNumberDecimalSeparator, NumberParameters} from './model';

type NumberSelectorProps = {
  parameters: NumberParameters;
  validationErrors: ValidationError[];
  onParametersChange: (updatedParameters: NumberParameters) => void;
};

const NumberDecimalSeparator = ({parameters, validationErrors, onParametersChange}: NumberSelectorProps) => {
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  return (
    <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')}>
      <SelectInput
        invalid={0 < decimalSeparatorErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={parameters.decimal_separator}
        onChange={decimal_separator => {
          if (isNumberDecimalSeparator(decimal_separator)) {
            onParametersChange({...parameters, decimal_separator});
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
      {decimalSeparatorErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {NumberDecimalSeparator};
