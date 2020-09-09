import React, {ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from 'theme';

enum ButtonSize {
  Small = 'small',
  Standard = 'standard',
}

enum ButtonLevel {
  Primary = 'primary',
  Secondary = 'secondary',
  Tertiary = 'tertiary',
  Danger = 'danger',
}

type ButtonProps = {
  /**
   * Level of the button defining it's color and outline.
   * Possible values are: primary, secondary, tertiary, danger and ghost
   */
  level: ButtonLevel;

  /**
   * When an action does not require primary dominance on the page.
   */
  ghost: boolean;

  /**
   * Use when the user cannot proceed or until an input is collected.
   */
  disabled: boolean;

  /**
   * Define the size of a button
   */
  size: ButtonSize;

  /**
   * Accessibility label to describe shortly the button
   */
  ariaLabel?: string;

  /**
   * Define which element is the label of this button for accessibility purposes
   */
  ariaLabelledBy?: string;

  /**
   * Define what element is describing this button for accessibility purposes
   */
  ariaDescribedBy?: string;

  /**
   * Children of the button
   */
  children: ReactNode;
} & React.ButtonHTMLAttributes<HTMLButtonElement> &
  AkeneoThemedProps;

const getHeight = ({size}: ButtonProps): string => {
  return size === ButtonSize.Small ? '20px' : '32px';
};

const getLineHeight = ({size}: ButtonProps): string => {
  return size === ButtonSize.Small ? '18px' : '30px';
};

const getPadding = ({size}: ButtonProps): string => {
  return size === ButtonSize.Small ? '0 10px' : '0 15px';
};

const getLevelColor = (level: ButtonLevel): string => {
  switch (level) {
    case ButtonLevel.Primary:
      return 'green';
    case ButtonLevel.Secondary:
      return 'blue';
    case ButtonLevel.Tertiary:
      return 'grey';
    case ButtonLevel.Danger:
      return 'red';
    default:
  }

  throw new Error(`Level "${level}" is not supported`);
};

const getColorStyle = ({level, ghost, disabled}: ButtonProps) => {
  const levelColor = getLevelColor(level);

  if (ghost) {
    return css`
      color: ${getColor(`${levelColor}${disabled ? 80 : 120}`)};
      background-color: white;
      border-color: ${getColor(`${levelColor}${disabled ? 60 : 100}`)};

      &:hover {
        color: ${getColor(`${levelColor}140`)};
        background-color: ${getColor(`${levelColor}20`)};
      }

      &:active {
        color: ${getColor(`${levelColor}140`)};
        background-color: white;
        border-color: ${getColor(`${levelColor}140`)};
      }
    `;
  }

  return css`
    color: white;
  `;
};

const Container = styled.button<ButtonProps>`
  font-size: ${(props: ButtonProps) => props.theme.fontSize.default};
  text-transform: uppercase;
  border-radius: 16px;

  padding: ${getPadding};
  ${getColorStyle}

  cursor: pointer;
  font-weight: 400;
  height: ${getHeight};
  line-height: ${getLineHeight};

  &:disabled {
    cursor: not-allowed;
  }

  &:focus {
    border-color: ${color.blue100};
  }
`;

const Button = ({children, ...props}: ButtonProps) => {
  return <Container {...props}>{children}</Container>;
};

export {Button};
