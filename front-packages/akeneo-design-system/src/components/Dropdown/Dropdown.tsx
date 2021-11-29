import React, {Children, cloneElement, HTMLAttributes, isValidElement, ReactNode, useRef} from 'react';
import styled from 'styled-components';
import {Overlay} from './Overlay/Overlay';
import {Item} from './Item/Item';
import {ItemCollection} from './ItemCollection/ItemCollection';
import {Header} from './Header/Header';
import {Title} from './Header/Title';
import {getColor} from '../../theme';
import {Surtitle} from './Surtitle/Surtitle';
import {Override} from '../../shared';

const Section = styled.div`
  background: ${getColor('white')};
  color: ${getColor('grey', 100)};
  height: 34px;
  line-height: 34px;
  padding: 0 20px;
  outline-style: none;
  white-space: nowrap;
  text-transform: uppercase;
  margin-top: 10px;
`;

const DropdownContainer = styled.div`
  position: relative;
  display: inline-flex;
`;

type DropdownProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The content of the Dropdown.
     */
    children?: ReactNode;
  }
>;

/**
 * The dropdown shows a list of options that can be used to select, filter or sort content.
 */
const Dropdown = ({children, ...rest}: DropdownProps) => {
  const ref = useRef<HTMLDivElement>(null);
  const decoratedChildren = Children.map(children, child => {
    if (!isValidElement(child) || child.type !== Overlay) return child;

    return cloneElement(child, {
      parentRef: ref,
    });
  });

  return (
    <DropdownContainer ref={ref} {...rest}>
      {decoratedChildren}
    </DropdownContainer>
  );
};

Overlay.displayName = 'Dropdown.Overlay';
Header.displayName = 'Dropdown.Header';
Title.displayName = 'Dropdown.Title';
ItemCollection.displayName = 'Dropdown.ItemCollection';
Item.displayName = 'Dropdown.Item';
Section.displayName = 'Dropdown.Section';

Dropdown.Overlay = Overlay;
Dropdown.Header = Header;
Dropdown.Item = Item;
Dropdown.Section = Section;
Dropdown.Title = Title;
Dropdown.ItemCollection = ItemCollection;
Dropdown.Surtitle = Surtitle;

export {Dropdown};
