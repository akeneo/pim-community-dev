import React from 'react';
import styled from 'styled-components';
import {useBooleanState} from '../../../../hooks';
import {MoreVerticalIcon} from '../../../../icons';
import {getColor} from '../../../../theme';
import {Dropdown as BaseDropdown} from '../../../Dropdown/Dropdown';
import {Section} from '../Section/Section';
import {Item} from '../Item/Item';
import {uuid} from '../../../../shared';
import {DropdownItem} from './DropdownItem';

const Container = styled(BaseDropdown)`
  display: block;
`;

const Button = styled.button`
  background: none;
  border: none;
  width: 100%;
  height: 40px;
  cursor: pointer;
  outline: none;

  :focus:not(:active) {
    box-shadow: inset 0 0 0 2px ${getColor('blue', 40)};
  }

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
`;

type Props = {
  children?: React.ReactNode;
};

const Dropdown = ({children}: Props) => {
  const [isOpen, open, close] = useBooleanState(false);

  const sections = React.Children.toArray(children).filter(
    (child): child is React.ReactElement<React.ComponentProps<typeof Section>, typeof Section> =>
      React.isValidElement(child) && child.type === Section
  );

  return (
    <Container>
      <Button onClick={open} title={'Navigation'}>
        <MoreVerticalIcon />
      </Button>
      {isOpen && (
        <BaseDropdown.Overlay onClose={close} verticalPosition="down">
          <BaseDropdown.ItemCollection>
            {sections.map(section =>
              React.Children.toArray(section.props.children)
                .filter(
                  (child): child is React.ReactElement<React.ComponentProps<typeof Item>, typeof Item> =>
                    React.isValidElement(child) && child.type === Item
                )
                .map(item => {
                  return <DropdownItem key={uuid()}>{item}</DropdownItem>;
                })
            )}
          </BaseDropdown.ItemCollection>
        </BaseDropdown.Overlay>
      )}
    </Container>
  );
};

export {Dropdown};
