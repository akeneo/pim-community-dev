import React, {forwardRef, ButtonHTMLAttributes} from 'react';
import styled, {css} from 'styled-components';

type ButtonProps = {
  size?: 'micro' | 'default';
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline';
} & ButtonHTMLAttributes<HTMLButtonElement>;

const StyledButton = styled.button<ButtonProps>`
  text-align: center;
  text-transform: uppercase;
  white-space: nowrap;
  outline: none;
  cursor: ${props => (props.disabled ? 'not-allowed' : 'pointer')};
  opacity: ${props => (props.disabled ? 0.5 : 1)};

  ${props => {
    switch (props.size) {
      case 'micro':
        return css`
          padding: 0 10px;
          height: 20px;
          line-height: 19px;
          border-radius: 10px;
          font-size: ${props.theme.fontSize.small};
          min-width: 60px;
        `;
      default:
        return css`
          padding: 0 15px;
          height: 32px;
          line-height: 30px;
          border-radius: 16px;
          font-size: ${props.theme.fontSize.default};
          min-width: 70px;
        `;
    }
  }}

  ${props => {
    switch (props.color) {
      case 'outline':
        return css`
          color: ${props.theme.color.grey120};
          background-color: white;
          border: 1px solid ${props.theme.color.grey80};
        `;
      default:
        return css`
          color: white;
          background-color: ${props.theme.color[`${props.color}100`]};
          border: 1px solid transparent;
        `;
    }
  }}
`;

const Button = forwardRef<HTMLButtonElement, ButtonProps>((props, ref) => <StyledButton ref={ref} {...props} />);

export {Button};
