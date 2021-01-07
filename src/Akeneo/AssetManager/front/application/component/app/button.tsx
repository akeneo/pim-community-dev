import React from 'react';
import styled from 'styled-components';
import {ArrowDownIcon, getColor, AkeneoThemedProps, useShortcut, Key} from 'akeneo-design-system';

type ButtonProps = {
  buttonSize?: 'micro' | 'medium' | 'default';
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline';
  isDisabled?: boolean;
};

export const ButtonContainer = styled.div`
  display: flex;
  > :not(:first-child) {
    margin-left: 10px;
  }
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

export const Button = React.forwardRef((props: ButtonProps & any, ref) => (
  <StyledButton ref={ref} {...props} onClick={props.isDisabled ? undefined : props.onClick} />
));

const StyledButton = styled.div<ButtonProps>`
  text-align: center;
  cursor: pointer;
  text-transform: uppercase;
  white-space: nowrap;

  ${(props: AkeneoThemedProps<ButtonProps>) => {
    switch (props.buttonSize) {
      case 'micro':
        return `
          padding: 0 10px;
          height: 20px;
          line-height: 19px;
          border-radius: 10px;
          font-size: ${props.theme.fontSize.small};
          min-width: 60px;
        `;
      case 'medium':
        return `
          padding: 0 15px;
          height: 24px;
          line-height: 23px;
          border-radius: 16px;
          font-size: ${props.theme.fontSize.default};
          min-width: 70px;
        `;
      default:
        return `
          padding: 0 15px;
          height: 32px;
          line-height: 30px;
          border-radius: 16px;
          font-size: ${props.theme.fontSize.default};
          min-width: 70px;
        `;
    }
  }}

  ${(props: AkeneoThemedProps<ButtonProps>) => {
    if ('outline' === props.color) {
      return `
        color: ${props.theme.color.grey120};
        background-color: white;
        border: 1px solid ${props.theme.color.grey80};
        `;
    }

    return `
      color: white;
      background-color: ${(props.theme.color as any)[props.color + '100']};
      border: 1px solid transparent;
    `;
  }}

  ${(props: AkeneoThemedProps<ButtonProps>) =>
    props.isDisabled &&
    `
      cursor: not-allowed;
      opacity: 0.5;
  `}
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

const StyledMultipleButton = styled(Button)`
  display: flex;
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

const DownButton = styled.span`
  display: flex;
  align-items: center;
  padding-left: 15px;
  color: ${getColor('white')};
`;

type Item = {
  label: string;
  title?: string;
  isDisabled?: boolean;
  action: () => void;
};

type MultipleButtonProps = {
  items: Item[];
  children: React.ReactNode;
} & ButtonProps;

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
          <StyledMultipleButton {...props} onClick={() => setOpen(true)}>
            <span>{children}</span>
            <DownButton>
              <ArrowDownIcon size={18} />
            </DownButton>
          </StyledMultipleButton>
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
