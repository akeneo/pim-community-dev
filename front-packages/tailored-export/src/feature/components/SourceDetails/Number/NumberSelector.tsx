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
    <Field label={translate('akeneo.tailored_export.column_details.sources.selection.collection_separator')}>
      <SelectInput
        invalid={0 < separatorErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={selection.separator}
        onChange={separator => {
          if (isNumberSeparator(separator)) {
            onSelectionChange({...selection, separator});
          }
        }}
      >
        {availableSeparators.map(availableSeparator => (
          <SelectInput.Option key={availableSeparator} title={availableSeparator} value={availableSeparator}>
            {availableSeparator}
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
