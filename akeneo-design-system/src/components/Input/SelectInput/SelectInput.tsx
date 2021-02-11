/**
 * <SelectInput {...args} value="Value1" placeholder="Placeholder" noResult={<NoResult>}>
 <SelectInput.Option value="value1">Value</SelectInput.Option>
 <SelectInput.Option value="value2" selected>Value1</SelectInput.Option>
 <SelectInput.Option value="value3">Value2</SelectInput.Option>
 <SelectInput.Option>Value3</SelectInput.Option>
 <SelectInput.Option>Value4</SelectInput.Option>
 <SelectInput.Option>Value5</SelectInput.Option>
 </SelectInput>
 */

/**
 * Au click on ouvre la dropdown
 * Je clique sur un element on ferme en changeant la value du select
 * Je click en dehors ça ferme le dropdown
 * Je fait joue joue avec mon keyboard ça change d'item selection
 * on affiche la croix uniquement au hover
 * Lorsqu'on click sur la croix on clear la value
 * Lorsque je recherche uniquement les matchs s'affichents
 * On peut chercher par label et par code
 * Si pas de result qu'affiche t'on
 */

import React, {ReactNode, useState, useRef, isValidElement, ReactElement} from 'react';
import styled, {css} from 'styled-components';
import {Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {TextInput} from '../../../components';
import {useBooleanState, useShortcut} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';

//TODO be sure to select the appropriate container element here
const SelectInputContainer = styled.div`
  & input[type='text'] {
    background: transparent;
    z-index: 2;
  }
`;

const InputContainer = styled.div`
  position: relative;
`;

const ValueOptionContainer = styled.div`
  position: absolute;
  top: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  padding: 0 20px;
`;

const OptionContainer = styled.div<{tall: boolean} & AkeneoThemedProps>`
  background: ${getColor('white')};
  height: ${({tall}) => (tall ? '44px' : '34px')};
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
    font-style: italic;
    font-weight: 700;
  }
  &:disabled {
    color: ${getColor('grey', 100)};
  }
`;

const OverlayContainer = styled.div`
  position: relative;
`;

type VerticalPosition = 'up' | 'down';

const Overlay = styled.div<
  {
    visible: boolean;
    verticalPosition: VerticalPosition;
  } & AkeneoThemedProps
>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 0 0 10px 0;
  position: absolute;
  // opacity: ${({visible}) => (visible ? 1 : 0)}; //TODO add visibility back
  transition: opacity 0.15s ease-in-out;
  z-index: 2;
  left: 0;
  right: 0;

  ${({verticalPosition}) =>
    'up' === verticalPosition
      ? css`
          bottom: 6px;
        `
      : css`
          top: 6px;
        `};
`;

const Backdrop = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
`;

const OptionCollection = styled.div`
  max-height: 320px;
`;

type SelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string>>,
  {
    /**
     * TODO.
     */
    placeholder?: string;

    children: ReactNode;
  }
>;

const getChildValue = (child: ReactElement) =>
  undefined !== child.props.value ? child.props.value : child.props.children;

const SelectInput = ({placeholder, value, children, onChange, readOnly, ...rest}: SelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const [dropdownIsOpen, openOverlay, closeOverlay] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);

  const filteredChildren = React.Children.toArray(children).filter((child): child is ReactElement => {
    if (!isValidElement(child)) return false;

    if (undefined === child.props.value && typeof child.props.children !== 'string') {
      throw new Error('An option that is not a string, should have a defined value');
    }

    const label = typeof child.props.children === 'string' ? child.props.children : '';
    const title = child.props.title ?? '';
    const optionValue = getChildValue(child) + label + title;

    return -1 !== optionValue.toLowerCase().indexOf(searchValue.toLowerCase());
  });

  const valueOption =
    filteredChildren.find(child => {
      const childrenValue = getChildValue(child);

      return value === childrenValue;
    }) ?? value;

  const handleEnter = () => {
    if (filteredChildren.length > 0 && onChange) {
      const value = getChildValue(filteredChildren[0]);

      onChange(value);
      handleBlur();
    }
  };

  const handleSearch = (value: string) => {
    setSearchValue(value);
  };

  const handleFocus = () => {
    openOverlay();
  };

  const handleOptionClick = (value: string) => () => {
    onChange && onChange(value);
    handleBlur();
  };

  const handleBlur = () => {
    setSearchValue('');
    closeOverlay();
    inputRef.current && inputRef.current.blur();
  };

  useShortcut(Key.Enter, handleEnter, inputRef);

  return (
    <SelectInputContainer {...rest}>
      <InputContainer>
        {null !== value && '' === searchValue && <ValueOptionContainer>{valueOption}</ValueOptionContainer>}
        <TextInput
          ref={inputRef}
          value={searchValue}
          readOnly={readOnly}
          placeholder={null === value ? placeholder : ''}
          onChange={handleSearch}
          onFocus={handleFocus}
        />
      </InputContainer>
      <OverlayContainer>
        {dropdownIsOpen && (
          <>
            <Backdrop data-testid="backdrop" onClick={handleBlur} />
            <Overlay onClose={handleBlur}>
              <OptionCollection>
                {filteredChildren.length === 0 ? (
                  <span>Empty result</span>
                ) : (
                  filteredChildren.map(child => {
                    const value = undefined !== child.props.value ? child.props.value : String(child.props.children);

                    return (
                      <OptionContainer onClick={handleOptionClick(value)}>{React.cloneElement(child)}</OptionContainer>
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

const Option = styled.span``;

SelectInput.Option = Option;

export {SelectInput};
