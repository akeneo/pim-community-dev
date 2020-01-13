import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Down from 'akeneoassetmanager/application/component/app/icon/down';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';

type ButtonProps = {
  buttonSize?: 'micro' | 'medium' | 'default';
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline';
  isDisabled?: boolean;
};

export const TransparentButton = styled.button`
  background: none;
  border: none;
  padding: 0;
  margin: 0;

  &:hover {
    cursor: pointer;
  }
`;

export const Button = (props: ButtonProps & any) => (
  <StyledButton {...props} onClick={props.isDisabled ? undefined : props.onClick} />
);

const StyledButton = styled.div<ButtonProps>`
  text-align: center;
  cursor: pointer;
  text-transform: uppercase;
  white-space: nowrap;

  ${(props: ThemedProps<ButtonProps>) => {
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
          min-width: 100px;
        `;
      default:
        return `
          padding: 0 15px;
          height: 32px;
          line-height: 30px;
          border-radius: 16px;
          font-size: ${props.theme.fontSize.default};
          min-width: 100px;
        `;
    }
  }}

  ${(props: ThemedProps<ButtonProps>) => {
    if ('outline' === props.color) {
      return `
        color: ${props.theme.color.grey120};
        background-color: white;
        border: 1px solid ${props.theme.color.grey80};
        `;
    }

    return `
      color: white;
      background-color: ${(props.theme.color as any)[props.color + '100']}
      border: 1px solid transparent;
    `;
  }}

  ${(props: ThemedProps<ButtonProps>) =>
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

const Item = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  text-transform: none;
  text-align: left;
  height: 34px;
  display: flex;
  align-items: center;
  cursor: pointer;
`;

const DownButton = styled.span`
  display: flex;
  align-items: center;
  padding-left: 15px;
`;

type MultipleButtonProps = {
  items: {
    label: string;
    action: () => void;
  }[];
  children: React.ReactNode;
} & ButtonProps;

export const MultipleButton = ({items, children, ...props}: MultipleButtonProps) => {
  const [isOpen, setOpen] = React.useState(false);
  if (0 === items.length) return null;

  return (
    <Container>
      {1 < items.length ? (
        <>
          <StyledMultipleButton {...props} onClick={() => setOpen(true)}>
            <span>{children}</span>
            <DownButton>
              <Down size={18} color={akeneoTheme.color.white} />
            </DownButton>
          </StyledMultipleButton>
          {isOpen && (
            <>
              <Backdrop onClick={() => setOpen(false)} />
              <Panel>
                {items.map(item => (
                  <Item
                    key={item.label}
                    onClick={() => {
                      setOpen(false);
                      item.action();
                    }}
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
