import React, {ChangeEvent, Ref, useCallback} from 'react';
import styled from 'styled-components';
import {Override} from '../../../shared';
import {LockIcon} from '../../../icons';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';

const TextareaInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
`;

const Textarea = styled.textarea<{invalid: boolean; readonly: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 120)};
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  width: 100%;
  padding: 15px;
  min-height: 100px;
  max-height: 400px;
  font-size: ${getFontSize('default')};
  line-height: 20px;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  box-sizing: border-box;
  outline-style: none;

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

const CharacterLeftLabel = styled.div`
  font-size: ${getFontSize('small')};
  align-self: flex-end;
  color: ${getColor('grey', 100)};
`;

type TextareaInputProps = Override<
  React.InputHTMLAttributes<HTMLTextAreaElement>,
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
    defaultValue?: string;

    /**
     * Placeholder displayed when the input is empty.
     */
    placeholder?: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * Label displayed under the field to display the remaining character counter.
     */
    characterLeftLabel?: string;
  }
>;

const TextareaInput = React.forwardRef<HTMLDivElement, TextareaInputProps>(
  (
    {characterLeftLabel, readOnly, onChange, defaultValue, invalid, ...rest}: TextareaInputProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleChange = useCallback(
      (event: ChangeEvent<HTMLTextAreaElement>) => {
        // TODO: fix next line (run unit test to see the error)
        if (!readOnly && onChange) onChange(event.currentTarget.value);
      },
      [readOnly, onChange]
    );

    return (
      <TextareaInputContainer ref={forwardedRef}>
        <Textarea
          onChange={handleChange}
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid}
          invalid={invalid}
          defaultValue={defaultValue}
          {...rest}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
        {characterLeftLabel && <CharacterLeftLabel>{characterLeftLabel}</CharacterLeftLabel>}
      </TextareaInputContainer>
    );
  }
);

export {TextareaInput};
