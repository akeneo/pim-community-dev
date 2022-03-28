import React from 'react';
import {availableDecimalSeparators, isNumberDecimalSeparator, isNumberTarget, NumberSourceParameter} from './model';
import {AttributeTargetParameterConfiguratorProps} from '../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';

const NumberConfigurator = ({
  target,
  attribute,
  onTargetAttributeChange,
  validationErrors,
}: AttributeTargetParameterConfiguratorProps) => {
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  const handleAttributeTargetChange = (updatedNumberSourceParameter: NumberSourceParameter) => {
    onTargetAttributeChange({...target, source_parameter: updatedNumberSourceParameter});
  };

  return (
    <>
      {attribute.decimals_allowed && (
        <Field label={translate('akeneo.tailored_import.data_mapping.target.parameters.decimal_separator.title')}>
          <SelectInput
            invalid={0 < decimalSeparatorErrors.length}
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={target.source_parameter.decimal_separator}
            onChange={decimal_separator => {
              if (isNumberDecimalSeparator(decimal_separator)) {
                handleAttributeTargetChange({...target.source_parameter, decimal_separator});
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
      )}
    </>
  );
};

export {NumberConfigurator};
