import React, {
  ReactNode,
  useState,
  useRef,
  isValidElement,
  ReactElement,
  KeyboardEvent,
  useCallback,
  SyntheticEvent,
} from 'react';
import styled from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps, Overlay} from '../common';
import {IconButton, TextInput} from '../../../components';
import {useBooleanState, useShortcut, VerticalPosition} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {ArrowDownIcon, CloseIcon} from '../../../icons';
import {usePagination} from '../../../hooks/usePagination';

const SelectInputContainer = styled.div<{value: string | null; readOnly: boolean} & AkeneoThemedProps>`
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
  background: ${getColor('white')};
`;

const ActionContainer = styled.div`
  position: absolute;
  right: 8px;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 2;
`;

const SelectedOptionContainer = styled.div<{readOnly: boolean; clearable: boolean} & AkeneoThemedProps>`
  position: absolute;
  top: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  padding: 0 ${({clearable}) => (clearable ? 68 : 38)}px 0 16px;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  box-sizing: border-box;
  color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
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
    background: ${getColor('grey', 20)};
    color: ${getColor('brand', 140)};
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

const Option = styled.span<{value: string}>`
  display: block;
  line-height: 34px;
  min-height: 34px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

type SelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string>>,
  (
    | {
        clearable?: false;
        readOnly: true;
        value: string | null;
      }
    | {
        clearable?: false;
        readOnly?: boolean;
        value: string | null;
        onChange: (newValue: string) => void;
      }
    | {
        clearable?: true;
        readOnly?: boolean;
        value: string | null;
        onChange: (newValue: string | null) => void;
      }
  ) & {
    /**
     * The placeholder displayed when no option is selected.
     */
    placeholder?: string;

    /**
     * The text displayed when no result was found.
     */
    emptyResultLabel: string;

    /**
     * Accessibility text for the clear button
     */
    clearLabel?: string;

    /**
     * Accessibility text for the open dropdown button
     */
    openLabel: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * The options.
     */
    children?: ReactNode;

    /**
     * Force the vertical position of the overlay.
     */
    verticalPosition?: VerticalPosition;

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
 * Select input allows the user to select content and data when the expected user input is composed of one option value.
 */
const SelectInput = ({
  id,
  placeholder,
  invalid,
  value,
  emptyResultLabel,
  children,
  onChange,
  clearable = true,
  clearLabel = '',
  openLabel,
  readOnly = false,
  verticalPosition,
  onNextPage,
  onSearchChange,
  'aria-labelledby': ariaLabelledby,
  ...rest
}: SelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const [dropdownIsOpen, openOverlay, closeOverlay] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);
  const firstOptionRef = useRef<HTMLDivElement>(null);
  const lastOptionRef = useRef<HTMLDivElement>(null);
  const selectedOptionRef = useRef<HTMLDivElement>(null);

  const validChildren = React.Children.toArray(children).filter((child): child is ReactElement<
    {value: string} & React.HTMLAttributes<HTMLSpanElement>
  > => isValidElement<{value: string}>(child));

  validChildren.reduce<string[]>((optionCodes: string[], child) => {
    if (optionCodes.includes(child.props.value)) {
      throw new Error(`Duplicate option value ${child.props.value}`);
    }

    optionCodes.push(child.props.value);

    return optionCodes;
  }, []);

  const filteredChildren = validChildren.filter(child => {
    const content = typeof child.props.children === 'string' ? child.props.children : '';
    const title = child.props.title ?? '';
    const value = child.props.value;
    const optionValue = value + content + title;

    return optionValue.toLowerCase().includes(searchValue.toLowerCase());
  });

  const currentValueElement =
    validChildren.find(child => {
      const childrenValue = child.props.value;

      return value === childrenValue;
    }) ?? value;

  const handleSearch = (value: string) => {
    onSearchChange?.(value);
    setSearchValue(value);
    openOverlay();
  };

  const handleOptionClick = (value: string) => () => {
    onChange?.(value);
    handleEscape();
  };

  const handleClear = (e: SyntheticEvent) => {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    onChange?.(null);
    e.preventDefault();
    inputRef.current?.focus();
  };

  const handleEscape = () => {
    setSearchValue('');
    closeOverlay();
    inputRef.current?.focus();
  };

  useShortcut(Key.Escape, handleEscape, inputRef);

  const handleInputKeyDown = useCallback(
    (event: KeyboardEvent<HTMLInputElement>) => {
      if (null !== event.currentTarget) {
        if (event.key === Key.Tab) {
          setSearchValue('');
          closeOverlay();
        }

        if (event.key === Key.ArrowDown) {
          event.preventDefault();

          if (dropdownIsOpen) {
            (firstOptionRef.current || selectedOptionRef.current)?.focus();
          } else if (!value) {
            onChange?.(validChildren[0].props.value);
          } else {
            const indexOfCurrentValue = validChildren.findIndex(child => child.props.value === value);
            if (indexOfCurrentValue < validChildren.length - 1) {
              onChange?.(validChildren[indexOfCurrentValue + 1].props.value);
            }
          }
        } else if (event.key === Key.ArrowUp) {
          event.preventDefault();

          if (!dropdownIsOpen && value) {
            const indexOfCurrentValue = validChildren.findIndex(child => child.props.value === value);
            if (indexOfCurrentValue > 0) {
              onChange?.(validChildren[indexOfCurrentValue - 1].props.value);
            }
          }
        } else if (event.key === Key.Enter) {
          event.preventDefault();
          if (!dropdownIsOpen) {
            openOverlay();
          }
        }
      }
    },
    [value, dropdownIsOpen]
  );

  React.useEffect(() => {
    if (dropdownIsOpen && searchValue === '') {
      (selectedOptionRef.current || firstOptionRef.current)?.focus();
    }
  }, [dropdownIsOpen, selectedOptionRef.current]);

  const handleOptionKeyDown = useCallback(
    (event: KeyboardEvent<HTMLDivElement>) => {
      if (null !== event.currentTarget) {
        if (event.key === Key.Tab) {
          setSearchValue('');
          closeOverlay();
        }
        if (([Key.ArrowDown, Key.ArrowUp, Key.Enter, Key.Escape] as string[]).includes(event.key)) {
          if (event.key === Key.ArrowDown) {
            const nextSibling = (event.currentTarget as HTMLElement).nextSibling as HTMLElement;
            nextSibling?.focus();
            event.preventDefault();
          }
          if (event.key === Key.ArrowUp) {
            const previousSibling = (event.currentTarget as HTMLElement).previousSibling as HTMLElement;
            previousSibling?.focus();
            event.preventDefault();
          }
          if (event.key === Key.Enter) {
            const value = (event.currentTarget.firstChild as HTMLElement)?.getAttribute('value') as string;
            onChange?.(value);
            handleEscape();
          }
          if (event.key === Key.Escape) {
            handleEscape();
          }
        } else {
          inputRef.current?.focus();
        }
      }
    },
    [onChange, value]
  );

  usePagination(containerRef, lastOptionRef, onNextPage, dropdownIsOpen);

  return (
    <SelectInputContainer readOnly={readOnly} value={value} {...rest}>
      <InputContainer>
        {null !== value && '' === searchValue && (
          <SelectedOptionContainer readOnly={readOnly} clearable={clearable}>
            {currentValueElement}
          </SelectedOptionContainer>
        )}
        <TextInput
          id={id}
          ref={inputRef}
          value={searchValue}
          readOnly={readOnly}
          invalid={invalid}
          placeholder={null === value ? placeholder : ''}
          onChange={handleSearch}
          onClick={e => {
            openOverlay();
            e.preventDefault();
          }}
          aria-labelledby={ariaLabelledby}
          onKeyDown={handleInputKeyDown}
        />
        {!readOnly && (
          <ActionContainer>
            {!dropdownIsOpen && null !== value && clearable && (
              <IconButton
                ghost="borderless"
                level="tertiary"
                size="small"
                icon={<CloseIcon />}
                title={clearLabel}
                onClick={handleClear}
                tabIndex={0}
              />
            )}
            <IconButton
              ghost="borderless"
              level="tertiary"
              size="small"
              icon={<ArrowDownIcon />}
              title={openLabel}
              onClick={openOverlay}
              onFocus={handleEscape}
              tabIndex={-1}
            />
          </ActionContainer>
        )}
      </InputContainer>
      {dropdownIsOpen && !readOnly && (
        <Overlay parentRef={inputRef} verticalPosition={verticalPosition} onClose={handleEscape}>
          <OptionCollection ref={containerRef}>
            {filteredChildren.length === 0 ? (
              <EmptyResultContainer>{emptyResultLabel}</EmptyResultContainer>
            ) : (
              filteredChildren.map((child, index) => {
                const childValue = child.props.value;
                let ref = undefined;
                switch (index) {
                  case 0:
                    ref = firstOptionRef;
                    break;
                  case filteredChildren.length - 1:
                    ref = lastOptionRef;
                    break;
                }
                if (value === childValue) {
                  ref = selectedOptionRef;
                }

                return (
                  <OptionContainer
                    data-testid={childValue}
                    key={childValue}
                    onClick={handleOptionClick(childValue)}
                    onKeyDown={handleOptionKeyDown}
                    tabIndex={0}
                    ref={ref}
                  >
                    {React.cloneElement(child)}
                  </OptionContainer>
                );
              })
            )}
          </OptionCollection>
        </Overlay>
      )}
    </SelectInputContainer>
  );
};

Option.displayName = 'SelectInput.Option';
SelectInput.Option = Option;

export {SelectInput};
