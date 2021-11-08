import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';

type sizeMode = 'small' | 'large';

type CoreButtonProps = {
  ariaLabel?: string;
  ariaLabelledBy?: string;
  ariaDescribedBy?: string;
  children: ReactNode;
  sizeMode?: sizeMode;
} & React.ButtonHTMLAttributes<HTMLButtonElement>;

const getHeight = ({sizeMode}: CoreButtonProps): string => {
  if (sizeMode === 'small') {
    return '24px';
  }
  return '32px';
};
const getLineHeight = ({sizeMode}: CoreButtonProps): string => {
  if (sizeMode === 'small') {
    return '22px';
  }
  return '30px';
};

const getPadding = ({sizeMode}: CoreButtonProps): string => {
  if (sizeMode === 'small') {
    return '0 10px';
  }
  return '0 15px';
};

const BasicButton = styled.button<CoreButtonProps>`
  border-radius: 16px;
  cursor: pointer;
  font-size: ${({theme}): string => theme.fontSize.default};
  font-weight: 400;
  height: ${getHeight};
  line-height: ${getLineHeight};
  text-transform: uppercase;
  padding: ${getPadding};
  border-width: 1px;
  border-style: solid;
  transition: background 0.1s ease, color 0.1s ease, border-color 0.1s ease;
  &:disabled {
    cursor: not-allowed;
  }
  &:focus {
    border-color: ${({theme}): string => theme.color.blue100};
  }
`;

const CoreButton = React.forwardRef<HTMLButtonElement, CoreButtonProps>(
  function CoreButton(
    {
      ariaDescribedBy,
      ariaLabel,
      ariaLabelledBy,
      children,
      disabled,
      onKeyDown,
      sizeMode,
      type = 'button',
      ...rest
    },
    forwardedRef: Ref<HTMLButtonElement>
  ) {
    // https://www.w3.org/TR/wai-aria-practices/#button
    const handleKeyDown = (
      event: React.KeyboardEvent<HTMLButtonElement>
    ): void => {
      if (onKeyDown && (event.keyCode === 32 || event.keyCode === 13)) {
        onKeyDown(event);
      }
    };
    return (
      <BasicButton
        aria-describedby={ariaDescribedBy}
        aria-disabled={disabled}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        disabled={disabled}
        onKeyDown={handleKeyDown}
        ref={forwardedRef}
        role='button'
        sizeMode={sizeMode}
        type={type}
        {...rest}>
        {children}
      </BasicButton>
    );
  }
);

export {CoreButton};
