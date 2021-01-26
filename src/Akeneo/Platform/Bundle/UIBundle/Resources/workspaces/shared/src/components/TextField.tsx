import React, {Ref} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {formatParameters} from '@akeneo-pim-community/shared';
import {FieldProps, TextInputProps, Field, TextInput, Helper} from 'akeneo-design-system';
import {ValidationError} from '../models';

type TextFieldProps = Omit<FieldProps, 'children'> &
  TextInputProps & {
    required?: boolean;
    errors?: ValidationError[];
  };

const TextField = React.forwardRef<HTMLInputElement, TextFieldProps>(
  (
    {required = false, errors = [], label, incomplete, locale, channel, ...inputProps}: TextFieldProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const translate = useTranslate();

    return (
      <Field
        label={required ? `${label} ${translate('pim_common.required_label')}` : label}
        incomplete={incomplete}
        locale={locale}
        channel={channel}
      >
        <TextInput {...inputProps} ref={forwardedRef} invalid={0 < errors.length} />
        {formatParameters(errors).map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
    );
  }
);

export {TextField};
