import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';

type ButtonProps = {
  buttonSize: 'micro' | 'medium' | 'default',
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline'
}

export const Button = styled.div<ButtonProps>`
  ${(props: ThemedProps<ButtonProps>) => {
    switch(props.buttonSize) {
      case 'micro':
        return `
          padding: 0 10px;
          height: 20px;
          line-height: 19px;
          border-raidus: 10px;
          font-size: ${props.theme.fontSize.small};
          min-width: 60px;
        `;
      case 'medium':
        return `
          padding: 0 15px;
          height: 24px;
          line-height: 23px;
          border-raidus: 16px;
          font-size: ${props.theme.fontSize.default};
          min-width: 100px;
        `;
      default:
        return `
          padding: 0 15px;
          height: 32px;
          line-height: 23px;
          border-raidus: 16px;
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
    `
  }}

  cursor: pointer;
  text-transform: uppercase;
`;
