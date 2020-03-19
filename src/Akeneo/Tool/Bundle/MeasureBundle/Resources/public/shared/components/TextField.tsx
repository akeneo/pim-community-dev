import React, {ChangeEventHandler, InputHTMLAttributes, useContext} from 'react';
import styled, {css} from 'styled-components';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';
import {useFocus} from 'akeneomeasure/shared/hooks/use-focus';

type InputProps = {
  invalid: boolean;
} & InputHTMLAttributes<HTMLInputElement>;

const Input = styled.input.attrs<InputProps>(() => ({
  className: 'AknTextField',
}))<InputProps>`
  border: 1px solid ${props => props.theme.color.grey80};
  padding: 0 15px;

  ${props =>
    props.invalid === true &&
    css`
      border-color: ${props => props.theme.color.red100};
    `};
`;

type TextFieldProps = {
  id: string;
  label: string;
  required?: boolean;
  autofocus?: boolean;

  value: string;
  onChange: ChangeEventHandler<HTMLInputElement>;
  errors?: ValidationError[];

  flag?: string;
};

const TextField = ({
  id,
  label,
  required = false,
  autofocus = false,
  value,
  onChange,
  errors,
  flag,
  ...props
}: TextFieldProps & InputHTMLAttributes<HTMLInputElement>) => {
  const __ = useContext(TranslateContext);
  const [focusRef] = useFocus();

  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-header">
        <label className="AknFieldContainer-label" htmlFor={id}>
          {label} {required && __('pim_common.required_label')}
        </label>
        {flag && <Flag localeCode={flag} />}
      </div>
      <div className="AknFieldContainer-inputContainer">
        <Input
          type="text"
          autoComplete="off"
          id={id}
          ref={autofocus ? focusRef : undefined}
          invalid={undefined !== errors && errors.length > 0}
          value={value}
          onChange={onChange}
          {...props}
        />
      </div>
      <InputErrors errors={errors} />
    </div>
  );
};

export {TextField};
