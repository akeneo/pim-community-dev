import React, {ChangeEvent} from 'react';
import styled from 'styled-components';
import {CheckIcon, DangerIcon, Theme} from 'akeneo-design-system';

type Status = 'error' | 'default' | 'success';

type InputProps = {
  type: string;
  title?: string;
  name?: string;
  placeholder?: string;
  status?: Status;
  onChange: (event: ChangeEvent<HTMLInputElement>) => void;
  statusMessage?: string;
  value?: string;
  theme?: Theme;
  isActive?: boolean;
};

const StyledInput = styled.input<{theme?: Theme}>`
  background: ${props => props.theme.color.white};
  border-radius: 2px;
  border: 1px solid ${props => props.theme.color.grey80};
  padding-left: 15px;
  padding-right: 15px;
  height: 40px;
  width: 100%;
  box-sizing: border-box;
  :focus {
    background: rgb(255, 255, 255);
    border-radius: 2px;
    border: 1px solid rgb(217, 221, 226);
    box-shadow: 0px 0px 0px 2px rgba(74, 144, 226, 0.3);
    outline: none;
  }
  &.error {
    border: 1px solid ${props => props.theme.color.red100};
  }
`;

const Container = styled.div<{theme?: Theme}>`
  width: 100%;
  display: flex;
  flex-direction: column;
  padding-bottom: 10px;

  @media (max-width: 768px) {
    padding-bottom: 30px;
  }
  input {
    margin-top: 0.6em;
  }
`;

const InputContainer = styled.div<{theme?: Theme}>`
  display: flex;
  justify-content: flex-end;
  align-items: center;
`;

const InputLabel = styled.label<{theme?: Theme}>`
  color: rgb(103, 118, 138);
  font-size: 13px;
  @media (max-width: 768px) {
    font-size: 15px;
  }
`;

const Message = styled.div<{theme?: Theme}>`
  color: ${props => props.theme.color.red100};
  font-size: 11px;
  display: flex;
  align-items: center;
  margin-top: 5px;
  max-width: 300px;
  p {
    margin-bottom: 0;
    margin-top: 0;
    margin-left: 5px;
    max-width: 300px;
  }
  svg {
    width: 15px;
    height: 15px;
    path {
      stroke: ${props => props.theme.color.red100};
    }
  }
  &.success {
    color: ${props => props.theme.color.green100};
    p {
      margin-bottom: 0;
      margin-top: 0;
      margin-left: 5px;
      max-width: 300px;
    }
    svg {
      path {
        stroke: ${props => props.theme.color.green100};
      }
    }
  }
`;

const showPassword = (type: string, enabled?: boolean) => {
  if (enabled) {
    return 'text';
  }
  return type;
};

const Input: React.FC<InputProps> = ({
  type,
  title,
  name,
  placeholder,
  onChange,
  value,
  status,
  statusMessage,
  isActive,
}) => {
  return (
    <Container>
      {placeholder && <InputLabel>{placeholder}</InputLabel>}
      <InputContainer>
        <StyledInput
          type={showPassword(type, isActive)}
          title={title}
          onChange={onChange}
          value={value}
          className={status}
          name={name}
        />
      </InputContainer>
      {status && statusMessage ? (
        <Message className={status}>
          {status === 'error' ? <DangerIcon /> : <CheckIcon />}
          <p>{statusMessage}</p>
        </Message>
      ) : (
        <div />
      )}
    </Container>
  );
};
export {Input};
