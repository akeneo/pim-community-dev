import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Field, SelectInput, Helper} from 'akeneo-design-system';
import {isNumberSeparator, availableSeparators, NumberSelection} from './model';
import React from 'react';

type NumberSelectorProps = {
  selection: NumberSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: NumberSelection) => void;
};

const NumberSelector = ({selection, validationErrors, onSelectionChange}: NumberSelectorProps) => {
  const translate = useTranslate();
  const separatorErrors = filterErrors(validationErrors, '[separator]');

  return (
    <Field label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator')}>
      <SelectInput
        invalid={0 < separatorErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={selection.decimal_separator}
        onChange={decimal_separator => {
          if (isNumberSeparator(decimal_separator)) {
            onSelectionChange({...selection, decimal_separator});
          }
        }}
      >
        {Object.entries(availableSeparators).map(([separator, name]) => (
          <SelectInput.Option
            key={separator}
            title={translate(
              `akeneo.tailored_export.column_details.sources.selection.number.decimal_separator.${name}`
            )}
            value={separator}
          >
            {translate(`akeneo.tailored_export.column_details.sources.selection.number.decimal_separator.${name}`)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {separatorErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {NumberSelector};
