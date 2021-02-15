import React, {ReactNode, useState, useRef, isValidElement, ReactElement, KeyboardEvent} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {IconButton, TextInput} from '../../../components';
import {useBooleanState, useShortcut} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {ArrowDownIcon, CloseIcon} from '../../../icons';

const SelectInputContainer = styled.div<{value: string | null; readOnly: boolean} & AkeneoThemedProps>`
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
  right: 10px;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
`;

const SelectedOptionContainer = styled.div<{readOnly: boolean} & AkeneoThemedProps>`
  position: absolute;
  top: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  padding: 0 20px;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  box-sizing: border-box;
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
  }
  &:hover {
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

const EmptyResultContainer = styled.div<{tall: boolean} & AkeneoThemedProps>`
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

type VerticalPosition = 'up' | 'down';

const OverlayContainer = styled.div`
  position: relative;
`;

const Overlay = styled.div<{verticalPosition: VerticalPosition} & AkeneoThemedProps>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0 10px 0;
  position: absolute;
  transition: opacity 0.15s ease-in-out;
  z-index: 2;
  left: 0;
  right: 0;

  ${({verticalPosition}) =>
    'up' === verticalPosition
      ? css`
          bottom: 46px;
        `
      : css`
          top: 6px;
        `};
`;

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
`;

const OptionCollection = styled.div`
  max-height: 320px;
  overflow-y: auto;
`;

const Option = styled.span<{value: string}>``;

type SelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string | null>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: string | null) => void;
      }
  ) & {
    /**
     * The props value of the selected option.
     */
    value: string | null;

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
    clearSelectLabel?: string;

    /**
     * Accessibility text for the open dropdown button
     */
    openSelectLabel?: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * The options.
     */
    children: ReactNode;

    /**
     * Force the vertical position of the overlay.
     */
    verticalPosition?: VerticalPosition;
  }
>;

/**
 * Select input allows the user to select content and data when the expected user input is composed of one.
 */
const SelectInput = ({
  id,
  placeholder,
  invalid,
  value,
  emptyResultLabel,
  children,
  onChange,
  clearSelectLabel = '',
  openSelectLabel = '',
  readOnly = false,
  verticalPosition = 'down',
  'aria-labelledby': ariaLabelledby,
  ...rest
}: SelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const [dropdownIsOpen, openOverlay, closeOverlay] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);

  const validChildren = React.Children.toArray(children).filter((child): child is ReactElement<
    {value: string} & React.HTMLAttributes<HTMLSpanElement>
  > => {
    return isValidElement<{value: string}>(child);
  });

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

    return -1 !== optionValue.toLowerCase().indexOf(searchValue.toLowerCase());
  });

  const currentValueElement =
    validChildren.find(child => {
      const childrenValue = child.props.value;

      return value === childrenValue;
    }) ?? value;

  const handleEnter = () => {
    if (filteredChildren.length > 0) {
      const value = filteredChildren[0].props.value;

      onChange?.(value);
      handleBlur();
    }
  };

  const handleSearch = (value: string) => {
    setSearchValue(value);
  };

  const handleClick = () => {
    if (dropdownIsOpen) {
      setSearchValue('');
      closeOverlay();
    } else {
      openOverlay();
    }
  };

  const handleOptionClick = (value: string) => () => {
    onChange?.(value);
    handleBlur();
  };

  const handleClear = () => {
    onChange?.(null);
  };

  const handleBlur = () => {
    setSearchValue('');
    closeOverlay();
    inputRef.current?.blur();
  };

  useShortcut(Key.Enter, handleEnter, inputRef);
  useShortcut(Key.Escape, handleBlur, inputRef);

  return (
    <SelectInputContainer readOnly={readOnly} value={value} {...rest}>
      <InputContainer>
        {null !== value && '' === searchValue && (
          <SelectedOptionContainer readOnly={readOnly}>{currentValueElement}</SelectedOptionContainer>
        )}
        <TextInput
          id={id}
          ref={inputRef}
          value={searchValue}
          readOnly={readOnly}
          invalid={invalid}
          placeholder={null === value ? placeholder : ''}
          onChange={handleSearch}
          onClick={handleClick}
          aria-labelledby={ariaLabelledby}
          data-testid="select_input"
        />
        {!readOnly && (
          <ActionContainer>
            {!dropdownIsOpen && null !== value && (
              <IconButton
                ghost="borderless"
                level="tertiary"
                size="small"
                icon={<CloseIcon />}
                title={clearSelectLabel}
                onClick={handleClear}
                tabIndex={0}
                onKeyDown={(event: KeyboardEvent<HTMLButtonElement | HTMLAnchorElement>) => {
                  if ([Key.Enter, Key.Space].includes(event.key as Key)) {
                    handleClear();
                  }
                }}
              />
            )}
            <IconButton
              ghost="borderless"
              level="tertiary"
              size="small"
              icon={<ArrowDownIcon />}
              title={openSelectLabel}
              onClick={openOverlay}
              tabIndex={0}
              onKeyDown={(event: KeyboardEvent<HTMLButtonElement | HTMLAnchorElement>) => {
                if ([Key.Enter, Key.Space].includes(event.key as Key)) {
                  openOverlay();
                }
              }}
            />
          </ActionContainer>
        )}
      </InputContainer>
      <OverlayContainer>
        {dropdownIsOpen && !readOnly && (
          <>
            <Backdrop data-testid="backdrop" onClick={handleBlur} />
            <Overlay verticalPosition={verticalPosition} onClose={handleBlur}>
              <OptionCollection>
                {filteredChildren.length === 0 ? (
                  <EmptyResultContainer>{emptyResultLabel}</EmptyResultContainer>
                ) : (
                  filteredChildren.map(child => {
                    const value = child.props.value;

                    return (
                      <OptionContainer key={value} onClick={handleOptionClick(value)}>
                        {React.cloneElement(child)}
                      </OptionContainer>
                    );
                  })
                )}
              </OptionCollection>
            </Overlay>
          </>
        )}
      </OverlayContainer>
    </SelectInputContainer>
  );
};

Option.displayName = 'SelectInput.Option';
SelectInput.Option = Option;

export {SelectInput};
