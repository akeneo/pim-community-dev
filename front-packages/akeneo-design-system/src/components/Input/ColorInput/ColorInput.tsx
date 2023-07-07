import React, {ChangeEvent, InputHTMLAttributes, Ref, useCallback} from 'react';
import styled, {css} from 'styled-components';
import {InputProps} from '../common/InputProps';
import {DangerIcon} from '../../../icons/DangerIcon';
import {LockIcon} from '../../../icons/LockIcon';
import {Override} from '../../../shared';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {isValidColor, convertColorToLongHexColor} from './Color';

const ColorInputContainer = styled.div<{readOnly: boolean} & AkeneoThemedProps>`
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 12px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  height: 74px;
  gap: 10px;
  outline-style: none;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
  overflow: hidden;
  ${({readOnly}) =>
    !readOnly &&
    css`
      &:focus-within {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
      }
    `}
`;

const ColorPicker = styled.input`
  width: 47px;
  height: 47px;
  border: none;
  padding: 0;
  ::-moz-color-swatch-wrapper {
    padding: 0;
  }
  ::-webkit-color-swatch-wrapper {
    padding: 0;
  }
  ::-webkit-color-swatch {
    border: none;
  }
  ::-moz-color-swatch {
    border: none;
  }
`;

const TextInput = styled.input<{readOnly: boolean} & AkeneoThemedProps>`
  border: none;
  flex: 1;
  outline: none;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  background: transparent;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
  height: 100%;

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  margin-left: 4px;
`;

const ErrorIcon = styled(DangerIcon)`
  padding: 0 15px 0 16px;
  box-sizing: content-box;
`;

type ColorInputProps = Override<
  Override<InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  (
    | {
        readOnly?: true;
      }
    | {
        readOnly?: false;
        onChange: (newValue: string) => void;
      }
  ) & {
    /**
     * Value of the input.
     */
    value: string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Defines if the input is valid or not.
     */
    invalid?: boolean;
  }
>;

/**
 * The ColorInput component allows the user to enter a color in hexadecimal format.
 */
const ColorInput: React.FC<ColorInputProps & {ref?: React.Ref<HTMLInputElement>}> = React.forwardRef<HTMLInputElement, ColorInputProps>(
  ({invalid, onChange, value, readOnly, ...rest}: ColorInputProps, forwardedRef: Ref<HTMLInputElement>) => {
    const handleChange = useCallback(
      (event: ChangeEvent<HTMLInputElement>) => {
        if (!readOnly && onChange) onChange(event.currentTarget.value);
      },
      [readOnly, onChange]
    );

    if (!value.startsWith('#')) {
      value = `#${value}`;
    }

    return (
      <ColorInputContainer invalid={invalid || !isValidColor(value)} readOnly={readOnly}>
        {isValidColor(value) ? (
          <ColorPicker
            type="color"
            value={convertColorToLongHexColor(value)}
            onChange={handleChange}
            disabled={readOnly}
          />
        ) : (
          <ErrorIcon role="alert" size={16} />
        )}
        <TextInput
          ref={forwardedRef}
          value={value}
          onChange={handleChange}
          type="text"
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid || !isValidColor(value)}
          {...rest}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
      </ColorInputContainer>
    );
  }
);

export {ColorInput};
