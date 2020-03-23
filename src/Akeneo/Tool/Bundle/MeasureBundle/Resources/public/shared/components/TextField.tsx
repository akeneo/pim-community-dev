import React, {ChangeEventHandler, useContext} from 'react';
import styled, {css} from 'styled-components';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';
import {useFocus} from 'akeneomeasure/shared/hooks/use-focus';
import {LockIcon} from 'akeneomeasure/shared/icons/LockIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

const Input = styled.input<{readOnly: boolean}>`
  background-color: transparent;
  border: none;
  flex: 1;

  outline: none;
  cursor: inherit;
`;

const InputContainer = styled.div<{readOnly: boolean; invalid: boolean}>`
  border: 1px solid ${props => props.theme.color.grey80};
  background-color: ${props => (props.readOnly ? props.theme.color.grey70 : 'inherit')};
  cursor: ${props => (props.readOnly ? 'not-allowed' : 'inherit')};
  height: 40px;
  display: flex;
  flex: 1;
  align-items: center;
  padding: 0 15px;

  ${props =>
    props.invalid === true &&
    css`
      border-color: ${props => props.theme.color.red100};
    `};

  ${Input} {
    color: ${props => (props.readOnly ? props.theme.color.grey100 : props.theme.color.grey140)};
  }
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
  readOnly,
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
      <InputContainer readOnly={readOnly} invalid={undefined !== errors && 0 < errors.length}>
        <Input
          ref={autofocus ? focusRef : undefined}
          readOnly={readOnly}
          id={id}
          type="text"
          autoComplete="off"
          {...props}
        />
        {readOnly && <LockIcon color={akeneoTheme.color.grey100} size={18} />}
      </InputContainer>
      <InputErrors errors={errors} />
    </div>
  );
};

export {TextField, Input, InputContainer};
