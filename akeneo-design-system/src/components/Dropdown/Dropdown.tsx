import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Overlay} from './Overlay/Overlay';
import {Item, SelectableItem, ImageItem, ItemLabel} from './Item/Item';
import {ItemCollection} from './ItemCollection/ItemCollection';
import {AkeneoThemedProps, getColor} from '../../theme';

const DropdownContainer = styled.div`
  position: relative;
  display: inline-block;
`;

type DropdownProps = {
  /**
   * The content of the Dropdown
   */
  children?: ReactNode;
};

const Action = styled.div`
  cursor: pointer;
  display: inline-block;
`;

const Header = styled.div`
  box-sizing: border-box;
  border-bottom: 1px solid ${getColor('brand', 100)};
  height: 44px;
  line-height: 44px;
  margin: 0 20px 10px 20px;
`;

const Content = styled.div``;

const Backdrop = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
`;

const Title = styled.div`
  font-size: 11px;
  text-transform: uppercase;
  color: ${getColor('brand', 100)};
`;

/**
 * The dropdown shows a list of options that can be used to select, filter or sort content.
 */
const Dropdown = ({children, ...rest}: DropdownProps) => {
  return <DropdownContainer {...rest}>{children}</DropdownContainer>;
};

Action.displayName = 'Dropdown.Action';
Header.displayName = 'Dropdown.Header';
Item.displayName = 'Dropdown.Item';
SelectableItem.displayName = 'Dropdown.SelectableItem';
ImageItem.displayName = 'Dropdown.ImageItem';
ItemLabel.displayName = 'Dropdown.ItemLabel';
Title.displayName = 'Dropdown.Title';
ItemCollection.displayName = 'Dropdown.ItemCollection';
Content.displayName = 'Dropdown.Content';
Backdrop.displayName = 'Dropdown.Backdrop';

Dropdown.Action = Action;
Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.SelectableItem = SelectableItem;
Dropdown.ImageItem = ImageItem;
Dropdown.ItemLabel = ItemLabel;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;
Dropdown.Content = Content;
Dropdown.Backdrop = Backdrop;

export {Dropdown};
