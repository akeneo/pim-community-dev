import React, {ChangeEvent, forwardRef, InputHTMLAttributes, Ref, useCallback} from 'react';
import styled from 'styled-components';
import {InputProps} from '../common';
import {Override} from '../../../shared';
import {LockIcon} from '../../../icons';
import {AkeneoThemedProps, getColor} from '../../../theme';

const InputContainer = styled.div``;
const Input = styled.input<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>``;
const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px;
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
     */
    value?: string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

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
  ({invalid, onChange, value, readOnly, ...rest}: DateInputProps, forwardedRef: Ref<HTMLInputElement>) => {
    const handleChange = useCallback((event: ChangeEvent<HTMLInputElement>) => {
      if (onChange) {
        onChange(event.currentTarget.value);
      }
      console.log('change', event.currentTarget.value);
    }, []);

    return (
      <InputContainer>
        <Input
          ref={forwardedRef}
          onChange={handleChange}
          type="text"
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid}
          invalid={invalid}
          title={value}
          {...rest}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
      </InputContainer>
    );
  }
);
export {DateInput};
