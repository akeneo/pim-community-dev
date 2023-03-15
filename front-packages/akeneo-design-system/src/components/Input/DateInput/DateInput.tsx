import React, {ChangeEvent, forwardRef, InputHTMLAttributes, Ref, useCallback, useRef} from 'react';
import styled, {css} from 'styled-components';
import {InputProps} from '../common';
import {Key, Override} from '../../../shared';
import {LockIcon, DateIcon} from '../../../icons';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {useShortcut} from '../../../hooks';

const InputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
`;

const Input = styled.input<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
  z-index: 0;
  width: 100%;
  height: 40px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  text-transform: uppercase;
  font-size: ${getFontSize('default')};
  line-height: 40px;
  padding: 0 ${({readOnly}) => (readOnly ? '35px' : '15px')} 0 15px;
  outline-style: none;
  cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};

  ${({readOnly}) =>
    readOnly &&
    css`
      overflow: hidden;
      text-overflow: ellipsis;
    `}

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }

  &::-webkit-datetime-edit-fields-wrapper {
    color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
  }

  &::-webkit-calendar-picker-indicator {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: auto;
    height: auto;
    color: transparent;
    background: transparent;
  }
`;

const IconContainer = styled.div<{readOnly: boolean} & AkeneoThemedProps>`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px 12px 12px 0;
  padding-left: 12px;
  pointer-events: none;
  z-index: 1;

  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
`;

const ReadOnlyIcon = styled(LockIcon)`
  color: ${getColor('grey', 100)};
`;

type DateInputProps = Override<
  Override<InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: string) => void;
      }
  ) & {
    /**
     * Value of the input.
     * The value is based on the ISO-8601 standard an always formatted yyyy-mm-dd.
     */
    value?: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * Callback called when the user hit enter on the field.
     */
    onSubmit?: () => void;
  }
>;
const DateInput = forwardRef<HTMLInputElement, DateInputProps>(
  ({invalid, onChange, value, readOnly, onSubmit, ...rest}: DateInputProps, forwardedRef: Ref<HTMLInputElement>) => {
    const internalRef = useRef<HTMLInputElement | null>(null);
    forwardedRef = forwardedRef ?? internalRef;

    const handleClick = useCallback(
      (event: MouseEvent) => {
        const input = event?.target as (HTMLInputElement & {showPicker?: () => void}) | null;
        !readOnly && input?.showPicker?.();
      },
      [readOnly]
    );

    const handleChange = useCallback(
      (event: ChangeEvent<HTMLInputElement>) => {
        if (!readOnly && onChange) {
          onChange(event.currentTarget.value);
        }
      },
      [readOnly, onChange]
    );

    const handleEnter = () => {
      !readOnly && onSubmit?.();
    };
    useShortcut(Key.Enter, handleEnter, forwardedRef);

    return (
      <InputContainer>
        <Input
          ref={forwardedRef}
          onChange={handleChange}
          type="date"
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid}
          invalid={invalid}
          title={value}
          value={value}
          pattern="\d{4}-\d{2}-\d{2}"
          onClick={handleClick}
          {...rest}
        />
        <IconContainer readOnly={readOnly}>
          {readOnly && <ReadOnlyIcon size={16} />}
          {!readOnly && <DateIcon size={16} />}
        </IconContainer>
      </InputContainer>
    );
  }
);
export {DateInput};
