import React, {useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {availableDateFormats, DateSelection, isDateFormat, isDefaultDateSelection} from './model';

type DateSelectorProps = {
  selection: DateSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: DateSelection) => void;
};

const DateSelector = ({selection, validationErrors, onSelectionChange}: DateSelectorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const formatErrors = filterErrors(validationErrors, '[format]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultDateSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
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
    </Collapse>
  );
};

export {DateSelector};
