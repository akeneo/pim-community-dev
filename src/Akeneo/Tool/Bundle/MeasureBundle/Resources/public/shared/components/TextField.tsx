import React, {ChangeEventHandler, useContext} from 'react';
import styled, {css} from 'styled-components';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';

const Input = styled.input.attrs(() => ({className: 'AknTextField'}))<{invalid: boolean}>`
  ${props =>
    props.invalid === true &&
    css`
      border-color: ${props => props.theme.color.red100};
    `}
`;

type TextFieldProps = {
  id: string;
  name: string;
  label: string;
  locale?: string;
  required?: boolean;
  flag?: string;

  value: string;
  onChange: ChangeEventHandler<Element>;

  errors?: ValidationError[];
};

const TextField = ({id, label, errors, propertyPath, required = false, flag, ...props}: TextFieldProps & any) => {
  const __ = useContext(TranslateContext);

  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-header">
        <label className="AknFieldContainer-label" htmlFor={id}>
          {label} {required && __('measurements.form.required_suffix')}
        </label>
        {flag && <Flag localeCode={flag} />}
      </div>
      <div className="AknFieldContainer-inputContainer">
        <Input type="text" autoComplete="off" invalid={errors && errors.length > 0} id={id} {...props} />
      </div>
      {errors && <InputErrors errors={errors} />}
    </div>
  );
};

export {TextField};
