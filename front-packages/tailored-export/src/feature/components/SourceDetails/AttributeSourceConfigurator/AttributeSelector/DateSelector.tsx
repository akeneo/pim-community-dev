import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {availableDateFormats, DateSelection, isDateFormat} from '../../../../models';

type DateSelectorProps = {
  selection: DateSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: DateSelection) => void;
};

const DateSelector = ({selection, validationErrors, onSelectionChange}: DateSelectorProps) => {
  const translate = useTranslate();
  const formatErrors = filterErrors(validationErrors, '[format]');

  return (
    <Field label={translate('akeneo.tailored_export.column_details.sources.selection.format')}>
      <SelectInput
        invalid={0 < formatErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={selection.format}
        onChange={format => {
          if (isDateFormat(format)) {
            onSelectionChange({...selection, format});
          }
        }}
      >
        {availableDateFormats.map(availableDateFormat => (
          <SelectInput.Option key={availableDateFormat} title={availableDateFormat} value={availableDateFormat}>
            {availableDateFormat}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {formatErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {DateSelector};
