import React from 'react';
import styled from 'styled-components';
import {ArrowDownIcon, AkeneoThemedProps, useShortcut, Key, Button, ButtonProps} from 'akeneo-design-system';

export const ButtonContainer = styled.div`
  display: flex;
  gap: 10px;
  align-items: center;
`;

export const TransparentButton = styled.button`
  background: none;
  border: none;
  padding: 0;
  margin: 0;

  &:hover {
    cursor: pointer;
  }
`;

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
`;

const Panel = styled.div`
  position: absolute;
  top: 32px;
  right: 0;
  background: white;
  display: flex;
  flex-direction: column;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  padding: 10px 20px;
  min-width: 180px;
`;

const Container = styled.div`
  position: relative;
`;

const Item = styled.div<{isDisabled?: boolean}>`
  color: ${(props: AkeneoThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: AkeneoThemedProps<void>) => props.theme.fontSize.default};
  text-transform: none;
  text-align: left;
  height: 34px;
  display: flex;
  align-items: center;
  cursor: ${props => (props.isDisabled ? 'not-allowed' : 'pointer')};
  opacity: ${props => (props.isDisabled ? 0.5 : 1)};
`;

type Item = {
  label: string;
  title?: string;
  isDisabled?: boolean;
  action: () => void;
};

type MultipleButtonProps = {
  items: Item[];
  children: string;
} & ButtonProps;

//TODO Replace this with DSM Dropdown
export const MultipleButton = ({items, children, ...props}: MultipleButtonProps) => {
  const [isOpen, setOpen] = React.useState(false);
  useShortcut(Key.Escape, () => setOpen(false));

  if (0 === items.length) return null;

  const onItemClick = (item: Item) => {
    if (!item.isDisabled) {
      setOpen(false);
      item.action();
    }
  };

  return (
    <Container>
      {1 < items.length ? (
        <>
          <Button {...props} onClick={() => setOpen(true)}>
            {children}
            <ArrowDownIcon />
          </Button>
          {isOpen && (
            <>
              <Backdrop onClick={() => setOpen(false)} />
              <Panel>
                {items.map(item => (
                  <Item
                    key={item.label}
                    title={item.title || item.label}
                    isDisabled={item.isDisabled}
                    onClick={() => onItemClick(item)}
                  >
                    {item.label}
                  </Item>
                ))}
              </Panel>
            </>
          )}
        </>
      ) : (
        <Button {...props} onClick={items[0].action}>
          {items[0].label}
        </Button>
      )}
    </Container>
  );
};
