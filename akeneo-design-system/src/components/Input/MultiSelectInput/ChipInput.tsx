import React, {Ref, useEffect} from 'react';
import styled from 'styled-components';
import {CloseIcon, LockIcon} from '../../../icons';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {IconButton} from '../../../components';
import {useBooleanState, useShortcut} from '../../../hooks';
import {Key} from '../../../shared';

const Container = styled.ul<AkeneoThemedProps & {invalid: boolean}>`
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  padding: 4px 30px 4px 4px;
  display: flex;
  flex-wrap: wrap;
  min-height: 40px;
  gap: 5px;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  position: relative;
  margin: 0;

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const Chip = styled.li<AkeneoThemedProps & {isSelected: boolean; readOnly: boolean}>`
  list-style-type: none;
  padding: 3px 15px;
  padding-left: ${({readOnly}) => (readOnly ? '15px' : '4px')};
  border: 1px ${getColor('grey', 80)} solid;
  background-color: ${({isSelected}) => (isSelected ? getColor('grey', 40) : getColor('grey', 20))};
  display: flex;
  align-items: center;
  height: 30px;
  box-sizing: border-box;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
`;

const Input = styled.input`
  width: 100%;
  height: 100%;
  border: 0;
  outline: 0;
  color: ${getColor('grey', 120)};
  background-color: transparent;
  font-size: ${getFontSize('default')};

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }
`;

const InputContainer = styled.li<AkeneoThemedProps>`
  list-style-type: none;
  color: ${getColor('grey', 120)};
  border: 0;
  flex: 1;
  padding: 0;
  align-items: center;
  display: flex;

  :first-child > ${Input} {
    padding-left: 11px;
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 11px;
  color: ${getColor('grey', 100)};
`;

const RemoveButton = styled(IconButton)`
  background-color: transparent;
  margin-left: -3px;
  margin-right: 1px;
  color: ${getColor('grey', 100)};
`;

type ChipValue = {
  code: string;
  label: string;
};

type ChipInputProps = {
  id?: string;
  value: ChipValue[];
  invalid?: boolean;
  placeholder?: string;
  readOnly?: boolean;
  searchValue: string;
  removeLabel: string;
  onRemove: (chipCode: string) => void;
  onSearchChange: (searchValue: string) => void;
  onFocus?: () => void;
};

const ChipInput = React.forwardRef<HTMLInputElement, ChipInputProps>(
  (
    {
      id,
      value,
      invalid,
      readOnly,
      placeholder,
      searchValue,
      removeLabel,
      onRemove,
      onSearchChange,
      onFocus,
    }: ChipInputProps,
    forwardedRef: Ref<HTMLInputElement>
  ) => {
    const [isLastSelected, selectLast, unselectLast] = useBooleanState();

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => onSearchChange(event.target.value);

    const handleBackspace = () => {
      if ('' !== searchValue || 0 === value.length) {
        return;
      }

      if (isLastSelected) {
        onRemove(value[value.length - 1].code);
      } else {
        selectLast();
      }
    };

    useEffect(() => {
      unselectLast();
    }, [value, searchValue]);

    useShortcut(Key.Backspace, handleBackspace, forwardedRef);

    return (
      <Container invalid={invalid} readOnly={readOnly}>
        {value.map((chip, index) => (
          <Chip key={chip.code} readOnly={readOnly} isSelected={index === value.length - 1 && isLastSelected}>
            {!readOnly && (
              <RemoveButton
                title={removeLabel}
                ghost="borderless"
                size="small"
                level="tertiary"
                icon={<CloseIcon />}
                onClick={() => onRemove(chip.code)}
              />
            )}
            {chip.label}
          </Chip>
        ))}
        <InputContainer>
          <Input
            type="text"
            id={id}
            value={searchValue}
            ref={forwardedRef}
            placeholder={value.length === 0 ? placeholder : undefined}
            onChange={handleChange}
            onBlur={unselectLast}
            aria-invalid={invalid}
            readOnly={readOnly}
            disabled={readOnly}
            onFocus={onFocus}
          />
          {readOnly && <ReadOnlyIcon size={16} />}
        </InputContainer>
      </Container>
    );
  }
);

export {ChipInput};
export type {ChipValue};
