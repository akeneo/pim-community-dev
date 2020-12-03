import React, {ChangeEvent} from 'react';
import styled from 'styled-components';
import {DangerIcon, Theme, ViewIcon} from 'akeneo-design-system';

type Status = 'error' | 'default';

type PasswordProps = {
  label?: string;
  name?: string;
  placeholder?: string;
  onChange: (event: ChangeEvent<HTMLInputElement>) => void;
  value?: string;
  theme?: Theme;
  status?: Status;
  statusMessage?: string;
  isPasswordVisible: boolean;
  makePasswordVisible?: (isPasswordVisible: boolean) => void;
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
    box-shadow: 0px 0px 0px 2px ${props => props.theme.color.blue100};
    background: ${props => props.theme.color.grey20};
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

const StyledIconBlock = styled.div<{theme?: Theme}>`
  display: inline-block;
  position: absolute;
  height: 40px;
  width: 2.5em;
  margin-top: 0.5em;
  &:focus {
    outline: none;
  }
`;

const StyledIconDiv = styled.div<{theme?: Theme}>`
  border: none;
  background: none;
  position: absolute;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 40px;
  width: 40px;
  &:focus {
    outline: none;
  }
`;

const Message = styled.div<{theme?: Theme}>`
  color: ${props => props.theme.color.red100};
  font-size: 11px;
  display: flex;
  align-items: center;
  max-width: 300px;

  p {
    margin-bottom: 0;
    margin-top: 0;
    margin-left: 5px;
    max-width: 280px;
  }

  svg {
    width: 15px;
    height: 15px;

    @media (max-width: 768px) {
      width: 24px;
      height: 24px;
    }

    path {
      stroke: ${props => props.theme.color.red100};
    }
  }

  &.default {
    visibility: hidden;

    svg {
      display: none;
    }
  }
`;

const StyledViewIcon = styled.div<{
  theme?: Theme;
  isIconActive: boolean;
}>`
  height: 100%;
  display: flex;
  align-items: center;

  svg {
    height: 100%;
    width: 100%;
    max-height: 16px;
    max-width: 16px;

    path {
      stroke: ${props => (props.isIconActive ? `${props.theme.color.blue100}` : '')};
    }

    &:focus {
      outline: none;
    }
  }
`;
const isPasswordType = (enabled: boolean) => {
  if (enabled) {
    return 'text';
  }
  return 'password';
};

const Password: React.FC<PasswordProps> = ({
  label,
  name,
  placeholder,
  onChange,
  value,
  status,
  statusMessage,
  isPasswordVisible,
  makePasswordVisible,
}) => {
  return (
    <Container>
      {placeholder && <InputLabel>{placeholder}</InputLabel>}
      <InputContainer>
        <StyledInput
          name={name}
          type={isPasswordType(isPasswordVisible)}
          title={label}
          onChange={onChange}
          value={value}
          className={status}
        />
        {makePasswordVisible && (
          <StyledIconBlock data-testid="make-password-visible" onClick={() => makePasswordVisible(!isPasswordVisible)}>
            <StyledIconDiv>
              <StyledViewIcon isIconActive={isPasswordVisible}>
                <ViewIcon />
              </StyledViewIcon>
            </StyledIconDiv>
          </StyledIconBlock>
        )}
      </InputContainer>
      <Message className={status && statusMessage ? 'error' : 'default'}>
        <DangerIcon />
        <p>{statusMessage}</p>
      </Message>
    </Container>
  );
};

export default Password;
