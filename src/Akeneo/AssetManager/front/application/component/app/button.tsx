import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type ButtonProps = {
  buttonSize?: 'micro' | 'medium' | 'default';
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline';
};

export const TransparentButton = styled.button`
  background: none;
  border: none;
  padding: 0;
  margin: none

  &:hover {
    cursor: pointer;
  }
`;

export const Button = styled.div<ButtonProps>`
  text-align: center;
  cursor: pointer;
  text-transform: uppercase;

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
`;
