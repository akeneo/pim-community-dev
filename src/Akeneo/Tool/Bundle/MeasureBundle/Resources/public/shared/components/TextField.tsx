import React, {ChangeEventHandler, useContext} from 'react';
import styled, {css} from 'styled-components';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';
import {useFocus} from 'akeneomeasure/shared/hooks/use-focus';

const Input = styled.input.attrs(() => ({className: 'AknTextField'}))<{invalid: boolean}>`
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
  name: string;
  label: string;
  locale?: string;
  required?: boolean;
  flag?: string;
  autofocus?: boolean;

  value: string;
  onChange: ChangeEventHandler<Element>;

  errors?: ValidationError[];
};

const TextField = ({
  id,
  label,
  errors,
  propertyPath,
  required = false,
  flag,
  autofocus = false,
  ...props
}: TextFieldProps & any) => {
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
          ref={autofocus ? focusRef : undefined}
          id={id}
          type="text"
          autoComplete="off"
          invalid={errors && errors.length > 0}
          {...props}
        />
      </div>
      {errors && <InputErrors errors={errors} />}
    </div>
  );
};

export {TextField};
