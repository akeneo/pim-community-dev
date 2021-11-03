import React, {isValidElement, ReactElement, useRef, useState} from 'react';
import styled from 'styled-components';
import {arrayUnique, Key, Override} from '../../../shared';
import {InputProps, Overlay} from '../common';
import {IconButton} from '../../../components';
import {useBooleanState, useShortcut, VerticalPosition} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {ArrowDownIcon} from '../../../icons';
import {ChipInput, ChipValue} from './ChipInput';
import {usePagination} from '../../../hooks/usePagination';

const MultiSelectInputContainer = styled.div<{value: string | null; readOnly: boolean} & AkeneoThemedProps>`
  width: 100%;

  & input[type='text'] {
    cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
    background: ${({value, readOnly}) => (null === value && readOnly ? getColor('grey', 20) : 'transparent')};

    &:focus {
      z-index: 2;
    }
  }
`;

const InputContainer = styled.div`
  position: relative;
`;

const ActionContainer = styled.div`
  position: absolute;
  right: 8px;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
`;

const OptionContainer = styled.div`
  background: ${getColor('white')};
  height: 34px;
  padding: 0 20px;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: ${getColor('grey', 120)};
  line-height: 34px;

  &:focus {
    color: ${getColor('grey', 120)};
  }
  &:hover {
    background: ${getColor('grey', 20)};
    color: ${getColor('brand', 140)};
  }
  &:active {
    color: ${getColor('brand', 100)};
    font-weight: 700;
  }
  &:disabled {
    color: ${getColor('grey', 100)};
  }
`;

const EmptyResultContainer = styled.div`
  background: ${getColor('white')};
  height: 20px;
  padding: 0 20px;
  align-items: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: ${getColor('grey', 100)};
  line-height: 20px;
  text-align: center;
`;

const OptionCollection = styled.div`
  max-height: 320px;
  overflow-y: auto;
`;

type OptionProps = {
  value: string;
  children: string;
} & React.HTMLAttributes<HTMLSpanElement>;

const Option = ({children, ...rest}: OptionProps) => <span {...rest}>{children}</span>;

type MultiMultiSelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string[]>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: string[]) => void;
      }
  ) & {
    /**
     * The props value of the selected option.
     */
    value: string[];

    /**
     * The placeholder displayed when no option is selected.
     */
    placeholder?: string;

    /**
     * The text displayed when no result was found.
     */
    emptyResultLabel: string;

    /**
     * Accessibility text for the open dropdown button.
     */
    openLabel: string;

    /**
     * Accessibility text for the remove chip button.
     */
    removeLabel: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * The options.
     */
    children?: ReactElement<OptionProps>[] | ReactElement<OptionProps>;

    /**
     * Force the vertical position of the overlay.
     */
    verticalPosition?: VerticalPosition;

    /**
     * Callback called when the user hit enter on the field.
     */
    onSubmit?: () => void;

    /**
     * Handler called when the next page is almost reached.
     */
    onNextPage?: () => void;

    /**
     * Handler called when the search value changed
     */
    onSearchChange?: (searchValue: string) => void;
  }
>;

/**
 * Multi select input allows the user to select content and data
 * when the expected user input is composed of multiple option values.
 */
const MultiSelectInput = ({
  id,
  placeholder,
  invalid,
  value,
  emptyResultLabel,
  children = [],
  onChange,
  removeLabel,
  onSubmit,
  openLabel,
  readOnly = false,
  verticalPosition,
  onNextPage,
  onSearchChange,
  'aria-labelledby': ariaLabelledby,
  ...rest
}: MultiMultiSelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const [dropdownIsOpen, openOverlay, closeOverlay] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const optionsContainerRef = useRef<HTMLDivElement>(null);
  const lastOptionRef = useRef<HTMLDivElement>(null);

  const validChildren = React.Children.toArray(children).filter((child): child is ReactElement<OptionProps> =>
    isValidElement<OptionProps>(child)
  );

  const indexedChips = validChildren.reduce<{[key: string]: ChipValue}>((indexedChips, {props: {value, children}}) => {
    if ('string' !== typeof children) {
      throw new Error('Multi select only accepts string as Option');
    }

    if (value in indexedChips) {
      throw new Error(`Duplicate option value ${value}`);
    }

    indexedChips[value] = {code: value, label: children};

    return indexedChips;
  }, {});

  const filteredChildren = validChildren.filter(({props}) => {
    const childValue = props.value;
    const optionValue = childValue + props.children;

    return !value.includes(childValue) && optionValue.toLowerCase().includes(searchValue.toLowerCase());
  });

  const handleEnter = () => {
    if (filteredChildren.length > 0 && dropdownIsOpen) {
      const newValue = filteredChildren[0].props.value;

      onChange?.(arrayUnique([...value, newValue]));
      setSearchValue('');
      onSearchChange?.('');
      closeOverlay();
    } else {
      !readOnly && onSubmit?.();
    }
  };

  const handleSearch = (value: string) => {
    setSearchValue(value);
    onSearchChange?.(value);
    openOverlay();
  };

  const handleRemove = (chipsCode: string) => {
    onChange?.(value.filter(value => value !== chipsCode));
  };

  const handleOptionClick = (newValue: string) => () => {
    onChange?.(arrayUnique([...value, newValue]));
    setSearchValue('');
    onSearchChange?.('');
    closeOverlay();
    inputRef.current?.focus();
  };

  const handleBlur = () => {
    setSearchValue('');
    onSearchChange?.('');
    closeOverlay();
    inputRef.current?.blur();
  };

  usePagination(optionsContainerRef, lastOptionRef, onNextPage, dropdownIsOpen);

  const handleFocus = () => openOverlay();

  useShortcut(Key.Enter, handleEnter, inputRef);
  useShortcut(Key.Escape, handleBlur, inputRef);

  return (
    <MultiSelectInputContainer ref={containerRef} readOnly={readOnly} value={value} {...rest}>
      <InputContainer>
        <ChipInput
          ref={inputRef}
          id={id}
          placeholder={placeholder}
          value={value.map(chipCode => indexedChips[chipCode] ?? {code: chipCode, label: chipCode})}
          searchValue={searchValue}
          removeLabel={removeLabel}
          readOnly={readOnly}
          invalid={invalid}
          onSearchChange={handleSearch}
          onRemove={handleRemove}
          onFocus={handleFocus}
        />
        {!readOnly && (
          <ActionContainer>
            <IconButton
              ghost="borderless"
              level="tertiary"
              size="small"
              icon={<ArrowDownIcon />}
              title={openLabel}
              onClick={openOverlay}
              onFocus={handleBlur}
              tabIndex={0}
            />
          </ActionContainer>
        )}
      </InputContainer>
      {dropdownIsOpen && !readOnly && (
        <Overlay parentRef={containerRef} onClose={handleBlur}>
          <OptionCollection ref={optionsContainerRef}>
            {0 === filteredChildren.length ? (
              <EmptyResultContainer>{emptyResultLabel}</EmptyResultContainer>
            ) : (
              filteredChildren.map((child, index) => {
                return (
                  <OptionContainer
                    key={child.props.value}
                    onClick={handleOptionClick(child.props.value)}
                    ref={index === filteredChildren.length - 1 ? lastOptionRef : undefined}
                  >
                    {React.cloneElement(child)}
                  </OptionContainer>
                );
              })
            )}
          </OptionCollection>
        </Overlay>
      )}
    </MultiSelectInputContainer>
  );
};

Option.displayName = 'MultiSelectInput.Option';
MultiSelectInput.Option = Option;

export {MultiSelectInput};
