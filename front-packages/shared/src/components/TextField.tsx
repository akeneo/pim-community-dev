import React, {ReactNode, Ref} from 'react';
import {FieldProps, TextInputProps, Field, TextInput, Helper} from 'akeneo-design-system';
import {useTranslate} from '../hooks';
import {ValidationError, formatParameters} from '../models';

type TextFieldProps = FieldProps &
  TextInputProps & {
    actions?: ReactNode;
    required?: boolean;
    errors?: ValidationError[];
  };

const TextField = React.forwardRef<HTMLInputElement, TextFieldProps>(
  (
    {
      actions,
      required = false,
      errors = [],
      label,
      incomplete,
      locale,
      channel,
      children,
      ...inputProps
    }: TextFieldProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const translate = useTranslate();

    return (
      <Field
        actions={actions}
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
        {children}
      </Field>
    );
  }
);

export {TextField};
