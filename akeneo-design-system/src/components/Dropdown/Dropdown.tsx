import {useToggleState} from 'hooks';
import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const DropdownContainer = styled.div`
  position: relative;
  display: inline-block;
`;

type DropdownProps = {
  /**
   * TODO.
   */
  children?: ReactNode;
};

const Action = styled.div`
  cursor: pointer;
  display: inline-block;
`;

const Overlay = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 0 0 10px 0;
  max-width: 320px;
  min-width: 150px;
  position: absolute;
  top: 0;
  left: 0;
  display: ${({isOpen}) => (isOpen ? 'block' : 'none')};
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

const Backdrop = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: ${({isOpen}) => (isOpen ? 'block' : 'none')};
`;

const Title = styled.div`
  font-size: 11px;
  text-transform: uppercase;
  color: ${getColor('brand', 100)};
`;

/**
 * TODO.
 */
const Dropdown = ({children, ...rest}: DropdownProps) => {
  const [isOpen, open, close] = useToggleState(false);

  const decoratedChildren = React.Children.map(children, child => {
    if (React.isValidElement(child) && Action === child.type) {
      return React.cloneElement(child, {onClick: open});
    }

    if (React.isValidElement(child) && Overlay === child.type) {
      return React.cloneElement(child, {isOpen});
    }

    if (React.isValidElement(child)) {
      console.error(`Dropdown only accept Dropdown.Action or Dropdown.Overlay as children. ${child.type} given.`);
    }

    return child;
  });

  return (
    <DropdownContainer {...rest}>
      <Backdrop onClick={close} isOpen={isOpen} />
      {decoratedChildren}
    </DropdownContainer>
  );
};

Action.displayName = 'Dropdown.Action';
Overlay.displayName = 'Dropdown.Overlay';
Header.displayName = 'Dropdown.Header';
Item.displayName = 'Dropdown.Item';
Title.displayName = 'Dropdown.Title';
ItemCollection.displayName = 'Dropdown.ItemCollection';
Content.displayName = 'Dropdown.Content';
Backdrop.displayName = 'Dropdown.Backdrop';

Dropdown.Action = Action;
Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;
Dropdown.Content = Content;
Dropdown.Backdrop = Backdrop;

export {Dropdown};
