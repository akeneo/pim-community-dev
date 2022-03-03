import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {ErrorAction, isValidErrorAction} from '../../models';

type ErrorActionInputProps = {
  value: ErrorAction;
  validationErrors: ValidationError[];
  onChange: (newValue: ErrorAction) => void;
};

const ErrorActionInput = ({value, validationErrors, onChange}: ErrorActionInputProps) => {
  const translate = useTranslate();

  return (
    <Field label={translate('akeneo.tailored_import.global_settings.error_action.label')}>
      <SelectInput
        invalid={validationErrors.length > 0}
        emptyResultLabel={translate('pim_common.no_result')}
        onChange={(newValue: string) => {
          if (isValidErrorAction(newValue)) {
            onChange(newValue);
          }
        }}
        openLabel={translate('pim_common.open')}
        value={value}
        clearable={false}
      >
        <SelectInput.Option value="skip_product">
          {translate('akeneo.tailored_import.global_settings.error_action.skip_product')}
        </SelectInput.Option>
        <SelectInput.Option value="skip_value">
          {translate('akeneo.tailored_import.global_settings.error_action.skip_value')}
        </SelectInput.Option>
      </SelectInput>
      <Helper level="info">{translate('akeneo.tailored_import.global_settings.error_action.helper_message')}</Helper>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {ErrorActionInput};
