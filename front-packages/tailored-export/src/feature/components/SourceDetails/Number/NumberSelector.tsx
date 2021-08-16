import React, {useState} from 'react';
import {Field, SelectInput, Helper, Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {isNumberDecimalSeparator, availableDecimalSeparators, NumberSelection} from './model';

type NumberSelectorProps = {
  selection: NumberSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: NumberSelection) => void;
};

const NumberSelector = ({selection, validationErrors, onSelectionChange}: NumberSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Field label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}>
        <SelectInput
          invalid={0 < decimalSeparatorErrors.length}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.decimal_separator}
          onChange={decimal_separator => {
            if (isNumberDecimalSeparator(decimal_separator)) {
              onSelectionChange({...selection, decimal_separator});
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
        {decimalSeparatorErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Collapse>
  );
};

export {NumberSelector};
