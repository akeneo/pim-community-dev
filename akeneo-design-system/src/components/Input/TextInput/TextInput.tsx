import React, {ChangeEvent, Ref, useCallback} from 'react';
import styled from 'styled-components';
import {InputProps} from '../InputProps';
import {LockIcon} from '../../../icons';
import {Override} from '../../../shared';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {useSkeleton} from '../../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../../Skeleton/Skeleton';

const TextInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
`;

const Input = styled.input<{readOnly: boolean; invalid: boolean} & SkeletonProps & AkeneoThemedProps>`
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

  &:focus {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  &::placeholder {
    color: ${getColor('grey', 100)};
  }

  ${applySkeletonStyle()}
`;

const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px;
  color: ${getColor('grey', 100)};
`;

const CharacterLeftLabel = styled.div<SkeletonProps & AkeneoThemedProps>`
  font-size: ${getFontSize('small')};
  line-height: ${getFontSize('small')};
  margin-top: 5px;
  align-self: flex-end;
  color: ${getColor('grey', 100)};

  ${applySkeletonStyle()}
`;

type TextInputProps = Override<
  React.InputHTMLAttributes<HTMLInputElement> & InputProps,
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
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * Label displayed under the field to display the remaining character counter.
     */
    characterLeftLabel?: string;
  }
>;

/**
 * The TextInput component allows the user to enter content and data when the expected input is a single line of text.
 */
const TextInput = React.forwardRef<HTMLInputElement, TextInputProps>(
  ({invalid, onChange, readOnly, characterLeftLabel, ...rest}: TextInputProps, forwardedRef: Ref<HTMLInputElement>) => {
    const handleChange = useCallback(
      (event: ChangeEvent<HTMLInputElement>) => {
        if (!readOnly && onChange) onChange(event.currentTarget.value);
      },
      [readOnly, onChange]
    );

    const skeleton = useSkeleton();

    return (
      <TextInputContainer>
        <Input
          ref={forwardedRef}
          onChange={handleChange}
          type="text"
          readOnly={readOnly}
          disabled={readOnly}
          aria-invalid={invalid}
          invalid={invalid}
          skeleton={skeleton}
          {...rest}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
        {characterLeftLabel && <CharacterLeftLabel skeleton={skeleton}>{characterLeftLabel}</CharacterLeftLabel>}
      </TextInputContainer>
    );
  }
);

export {TextInput};
