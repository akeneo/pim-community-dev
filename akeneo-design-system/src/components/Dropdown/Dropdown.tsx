import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Overlay} from './Overlay/Overlay';
import {Item} from './Item/Item';
import {ItemCollection} from './ItemCollection/ItemCollection';
import {Header} from './Header/Header';
import {Title} from './Header/Title';

const DropdownContainer = styled.div`
  position: relative;
  display: inline-block;
`;

type DropdownProps = {
  /**
   * The content of the Dropdown.
   */
  children?: ReactNode;
};

/**
 * The dropdown shows a list of options that can be used to select, filter or sort content.
 */
const Dropdown = ({children, ...rest}: DropdownProps) => {
  return <DropdownContainer {...rest}>{children}</DropdownContainer>;
};

Overlay.displayName = 'Dropdown.Overlay';
Header.displayName = 'Dropdown.Header';
Title.displayName = 'Dropdown.Title';
ItemCollection.displayName = 'Dropdown.ItemCollection';
Item.displayName = 'Dropdown.Item';

Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;

export {Dropdown};
