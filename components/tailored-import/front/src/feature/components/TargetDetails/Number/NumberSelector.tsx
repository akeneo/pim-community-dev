import React, {useState} from 'react';
import {Field, SelectInput, Helper, Collapse, Pill} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {availableDecimalSeparators, isDefaultNumberConfiguration, isNumberDecimalSeparator, NumberConfiguration} from './model';

type NumberSelectorProps = {
  configuration: NumberConfiguration;
  validationErrors: ValidationError[];
  onConfigurationChange: (updatedConfiguration: NumberConfiguration) => void;
};

const NumberSelector = ({configuration, validationErrors, onConfigurationChange}: NumberSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_import.column_details.sources.configuration.title')}
          {0 === validationErrors.length && !isDefaultNumberConfiguration(configuration) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Field label={translate('akeneo.tailored_import.column_details.sources.configuration.decimal_separator.title')}>
        <SelectInput
          invalid={0 < decimalSeparatorErrors.length}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={configuration.decimal_separator}
          onChange={decimal_separator => {
            if (isNumberDecimalSeparator(decimal_separator)) {
              onConfigurationChange({...configuration, decimal_separator});
            }
          }}
        >
          {Object.entries(availableDecimalSeparators).map(([separator, name]) => (
            <SelectInput.Option
              key={separator}
              title={translate(`akeneo.tailored_import.column_details.sources.configuration.decimal_separator.${name}`)}
              value={separator}
            >
              {translate(`akeneo.tailored_import.column_details.sources.configuration.decimal_separator.${name}`)}
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
