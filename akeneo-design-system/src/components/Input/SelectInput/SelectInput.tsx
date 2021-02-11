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

import React, {ReactNode, useState, useRef, isValidElement} from 'react';
import styled from 'styled-components';
import {Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {Dropdown, Key, TextInput} from '../../..';
import {useBooleanState, useShortcut} from '../../../hooks';

//TODO be sure to select the appropriate container element here
const SelectInputContainer = styled.div`
  
`;


const Option = styled(Dropdown.Item)`

`;

type SelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string>>,
  {
    /**
     * TODO.
     */
    placeholder?: string;

    children: ReactNode;
  }
>;

const SelectInput = ({placeholder, value, children, ...rest}: SelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>();
  const [dropdownIsOpen, openDropdown, closeDropdown] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);
  const handleFirstElement = () => {

  };

  const handleSearch = (value: string) => {
    setSearchValue(value);
  };

  const handleFocus = () => {
    openDropdown();
  };

  useShortcut(Key.Enter, handleFirstElement, inputRef);

  return (
    <SelectInputContainer>
      <TextInput ref={inputRef} value={value} placeholder={placeholder} onChange={handleSearch} onFocus={handleFocus} />
      <Dropdown>
        {dropdownIsOpen && (
          <Dropdown.Overlay onClose={closeDropdown}>
            <Dropdown.ItemCollection>
              {React.Children.map(children, child => {
                if (!isValidElement(child)) return;

                return React.cloneElement(child);
              })}
            </Dropdown.ItemCollection>
          </Dropdown.Overlay>
        )}
      </Dropdown>

      {children}
    </SelectInputContainer>
  );
};

export {SelectInput};
