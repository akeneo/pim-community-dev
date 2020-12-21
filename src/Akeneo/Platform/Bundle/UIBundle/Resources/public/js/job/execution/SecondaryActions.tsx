import React, {ReactNode, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {getColor, IconButton, MoreIcon} from 'akeneo-design-system';
import {useToggleState} from '@akeneo-pim-community/shared';

const Container = styled.div`
  position: relative;
`;

const Overlay = styled.div`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 0 0 10px 0;
  max-width: 320px;
  min-width: 150px;
  position: absolute;
  z-index: 1;
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
const Action = styled.div``;

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

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
`;

const SecondaryActions = ({title, children}: {title: string; children: ReactNode}) => {
  const [isOpen, open, close] = useToggleState(false);
  const decoratedChildren = React.Children.map(children, child => {
    if (!React.isValidElement(child)) return null;

    return React.cloneElement(child, {
      onClick: (event: SyntheticEvent) => {
        close();

        if (child.props.onClick) child.props.onClick(event);
      },
    });
  });

  if (!decoratedChildren) return null;

  return (
    <Container>
      <Action>
        <IconButton title={title} icon={<MoreIcon />} onClick={open} ghost={'borderless'} />
      </Action>
      {isOpen && (
        <>
          <Backdrop onClick={close} />
          <Overlay>
            <Header>
              <Title>{title}</Title>
            </Header>
            <ItemCollection>
              {decoratedChildren.map(item => (
                <Item>{item}</Item>
              ))}
            </ItemCollection>
          </Overlay>
        </>
      )}
    </Container>
  );
};

export {SecondaryActions};
