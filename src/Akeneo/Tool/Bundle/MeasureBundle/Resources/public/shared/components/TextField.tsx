import React, {ChangeEventHandler, InputHTMLAttributes, RefObject, useContext} from 'react';
import styled, {css, ThemeContext} from 'styled-components';
import {Flag} from 'akeneomeasure/shared/components/Flag';
import {LockIcon} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError, InputErrors} from '@akeneo-pim-community/shared';

const Container = styled.div`
  :not(:last-child) {
    margin-bottom: 20px;
  }
`;

const Input = styled.input`
  background-color: transparent;
  border: none;
  flex: 1;
  outline: none;
  cursor: inherit;
`;

const InputContainer = styled.div<{readOnly?: boolean; invalid: boolean}>`
  border: 1px solid ${props => props.theme.color.grey80};
  background-color: ${props => (props.readOnly ? props.theme.color.grey60 : 'inherit')};
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
  label: string;
  required?: boolean;
  readOnly?: boolean;

  value: string;
  onChange?: ChangeEventHandler<HTMLInputElement>;
  errors?: ValidationError[];

  flag?: string;
};

const TextField = React.forwardRef(
  (
    {
      id,
      label,
      required = false,
      readOnly = false,
      value,
      onChange,
      errors,
      flag,
      ...props
    }: TextFieldProps & InputHTMLAttributes<HTMLInputElement>,
    ref: RefObject<HTMLInputElement>
  ) => {
    const __ = useTranslate();
    const akeneoTheme = useContext(ThemeContext);

    return (
      <Container>
        <div className="AknFieldContainer-header">
          <label className="AknFieldContainer-label" htmlFor={id}>
            {label} {required && __('pim_common.required_label')}
          </label>
          {flag && <Flag localeCode={flag} />}
        </div>
        <InputContainer readOnly={readOnly} invalid={undefined !== errors && 0 < errors.length}>
          <Input
            ref={ref}
            readOnly={readOnly}
            id={id}
            value={value}
            onChange={onChange}
            type="text"
            autoComplete="off"
            {...props}
          />
          {readOnly && <LockIcon color={akeneoTheme.color.grey100} size={18} />}
        </InputContainer>
        <InputErrors errors={errors} />
      </Container>
    );
  }
);

export {TextField, Input, InputContainer};
