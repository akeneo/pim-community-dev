import React, {forwardRef, ButtonHTMLAttributes} from 'react';
import styled, {css} from 'styled-components';

type ButtonProps = {
  size?: 'micro' | 'default';
  color?: 'green' | 'blue' | 'red' | 'grey';
  outline?: boolean;
} & ButtonHTMLAttributes<HTMLButtonElement>;

const StyledButton = styled.button<ButtonProps>`
  text-align: center;
  text-transform: uppercase;
  white-space: nowrap;
  outline: none;
  opacity: ${props => (props.disabled ? 0.4 : 1)};
  cursor: ${props => (props.disabled ? 'not-allowed' : 'pointer')};

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
    if (props.outline) {
      return css`
        color: ${props.theme.color[`${props.color}100`]};
        background-color: white;
        border: 1px solid ${props.theme.color[`${props.color}100`]};

        :not(:disabled) {
          :hover {
            background-color: ${props.theme.color.grey60};
            color: ${props.theme.color[`${props.color}120`]};
            border-color: ${props.theme.color[`${props.color}120`]};
          }
          :active {
            background-color: ${props.theme.color.grey60};
            color: ${props.theme.color[`${props.color}140`]};
            border-color: ${props.theme.color[`${props.color}140`]};
          }
        }
      `;
    } else {
      return css`
        color: white;
        background-color: ${props.theme.color[`${props.color}100`]};
        border: 1px solid transparent;

        :not(:disabled) {
          :hover {
            background-color: ${props.theme.color[`${props.color}120`]};
          }
          :active {
            background-color: ${props.theme.color[`${props.color}140`]};
          }
        }
      `;
    }
  }}
`;

const Button = forwardRef<HTMLButtonElement, ButtonProps>((props, ref) => <StyledButton ref={ref} {...props} />);

Button.defaultProps = {
  color: 'green',
  size: 'default',
  outline: false,
};

const TransparentButton = styled.button`
  background: none;
  display: flex;
  padding: 0;
  border: none;
  outline: none;
  cursor: pointer;
`;

export {Button, TransparentButton};
