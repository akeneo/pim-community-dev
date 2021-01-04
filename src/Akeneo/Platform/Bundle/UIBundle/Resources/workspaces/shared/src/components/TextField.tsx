import React, {Ref} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {FieldProps, TextInputProps, Field, TextInput} from 'akeneo-design-system';
import {ValidationError} from '../models';
import {inputErrors} from './InputErrors';

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
        {inputErrors(translate, errors)}
      </Field>
    );
  }
);

export {TextField};
