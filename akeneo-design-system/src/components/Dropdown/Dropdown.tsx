import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const DropdownContainer = styled.div``;

type DropdownProps = {
  /**
   * TODO.
   */
  children?: ReactNode;
};

const Action = styled.div``;

const Overlay = styled.div`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 0 0 10px 0;
  max-width: 320px;
  min-width: 150px;
  position: absolute;
`;

const Header = styled.div`
  box-sizing: border-box;
  border-bottom: 1px solid ${getColor('brand', 100)};
  height: 44px;
  line-height: 44px;
  margin: 0 20px 10px 20px;
`;

const ItemCollection = styled.div`
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
`;
const Content = styled.div``;

const Item = styled.div`
  background: ${getColor('white')};
  color: ${getColor('grey', 120)};
  height: 34px;
  line-height: 34px;
  padding: 0 20px;
  cursor: pointer;

  &:hover {
    background: ${getColor('grey', 20)};
    color: ${getColor('brand', 140)};
  }

  &:active {
    color: ${getColor('brand', 100)};
    font-style: italic;
  }

  &:disabled {
    color: ${getColor('grey', 100)};
  }

  &:focus {
    color: ${getColor('grey', 120)};
  }
`;

const Title = styled.div`
  font-size: 11px;
  text-transform: uppercase;
  color: ${getColor('brand', 100)};
`;

const Button = styled.div``;

/**
 * TODO.
 */
const Dropdown = ({children, ...rest}: DropdownProps) => {
  return <DropdownContainer {...rest}>{children}</DropdownContainer>;
};

Dropdown.Action = Action;
Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.Button = Button;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;
Dropdown.Content = Content;

export {Dropdown};
