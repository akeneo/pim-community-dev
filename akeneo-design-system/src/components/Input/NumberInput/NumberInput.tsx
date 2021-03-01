import React, {ChangeEvent, useCallback, useRef} from 'react';
import styled from 'styled-components';
import {InputProps} from '../InputProps';
import {ArrowDownIcon, ArrowUpIcon, LockIcon} from '../../../icons';
import {Override} from '../../../shared';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';

const NumberInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
`;

const Input = styled.input<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
  width: 100%;
  height: 40px;
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('default')};
  line-height: 40px;
  padding: 0 15px;
  box-sizing: border-box;
  outline-style: none;
  appearance: textfield;

  &::-webkit-inner-spin-button,
  &::-webkit-outer-spin-button {
    -webkit-appearance: none;
  }

  &:focus {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  &::placeholder {
    color: ${getColor('grey', 100)};
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px;
  color: ${getColor('grey', 100)};
`;

const IncrementIconContainer = styled.div`
  position: absolute;
  right: 0;
  top: 0;
  margin: 0 12px;
  display: flex;
  flex-direction: column;
  height: 100%;
  justify-content: center;
  cursor: pointer;
`;

type NumberInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
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
     * Value of the input (it's a string to be able to manage weird cases externally)
     */
    value: string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * The minimum value to accept for this input.
     * This attribute is not used to validate the field nor constriant user input.
     */
    min?: number;

    /**
     * The maximum value to accept for this input.
     * This attribute is not used to validate the field nor constriant user input.
     */
    max?: number;

    /**
     * A stepping interval to use when using up and down arrows to adjust the value, as well as for validation
     */
    step?: number;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;
  }
>;

/**
 * Number input allows the user to enter content and data when the expected user input is only numbers.
 */
const NumberInput = React.forwardRef<HTMLInputElement, NumberInputProps>(
  ({invalid, onChange, readOnly, step, ...rest}: NumberInputProps, forwardedRef) => {
    const internalRef = useRef<HTMLInputElement | null>(null);
    forwardedRef = forwardedRef ?? internalRef;

    const handleChange = useCallback(
      (event: ChangeEvent<HTMLInputElement>) => {
        if (!readOnly && onChange) onChange(event.currentTarget.value);
      },
      [readOnly, onChange]
    );

    const handleIncrement = useCallback(() => {
      if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
        forwardedRef.current.stepUp(step);
        onChange(forwardedRef.current.value);
      }
    }, [forwardedRef, step]);
    const handleDecrement = useCallback(() => {
      if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
        forwardedRef.current.stepDown(step);
        onChange(forwardedRef.current.value);
      }
    }, [forwardedRef, step]);

    return (
      <NumberInputContainer>
        <Input
          ref={forwardedRef}
          onChange={handleChange}
          type="number"
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid}
          invalid={invalid}
          autoComplete="off"
          {...rest}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
        {!readOnly && (
          <IncrementIconContainer>
            <ArrowUpIcon size={16} data-testid="increment-number-input" onClick={handleIncrement} />
            <ArrowDownIcon size={16} data-testid="decrement-number-input" onClick={handleDecrement} />
          </IncrementIconContainer>
        )}
      </NumberInputContainer>
    );
  }
);

export {NumberInput};
export type {NumberInputProps};
