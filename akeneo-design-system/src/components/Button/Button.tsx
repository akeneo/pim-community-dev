import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from 'theme';
import {Key} from 'shared/key';
import {useShortcut} from 'hooks/use-shortcut';

enum ButtonSize {
  Small = 'small',
  Default = 'default',
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
   * Function called when the user clicks on the button or hit enter when focused
   */
  onClick: () => void;

  /**
   * Accessibility label to describe shortly the button
   */
  ariaLabel?: string;

  /**
   * Define which element is the label of this button for accessibility purposes. Expect a DOM node id.
   */
  ariaLabelledBy?: string;

  /**
   * Define what element is describing this button for accessibility purposes. Expect a DOM node id.
   */
  ariaDescribedBy?: string;

  /**
   * Children of the button
   */
  children: ReactNode;
} & React.ButtonHTMLAttributes<HTMLButtonElement>;

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

const getColorStyle = ({level, ghost, disabled}: {level: ButtonLevel; ghost: boolean; disabled: boolean}) => {
  const levelColor = getLevelColor(level);

  if (ghost) {
    return css`
      color: ${getColor(`${levelColor}${disabled ? 80 : 120}`)};
      background-color: white;
      border-color: ${getColor(`${levelColor}${disabled ? 60 : 100}`)};

      &:hover {
        color: ${getColor(`${levelColor}140`)};
        background-color: ${getColor(`${levelColor}20`)};
        border-color: ${getColor(`${levelColor}120`)};
      }

      &:active {
        color: ${getColor(`${levelColor}140`)};
        border-color: ${getColor(`${levelColor}140`)};
      }
    `;
  }

  return css`
    color: white;
    background-color: ${getColor(`${levelColor}${disabled ? 40 : 100}`)};

    &:hover {
      background-color: ${getColor(`${levelColor}120`)};
    }

    &:active {
      background-color: ${getColor(`${levelColor}140`)};
    }
  `;
};

const Container = styled.button<
  {
    level: ButtonLevel;
    ghost: boolean;
    disabled: boolean;
    size: ButtonSize;
  } & AkeneoThemedProps
>`
  border-width: 1px;
  border-style: ${(props) => (props.ghost ? 'solid' : 'none')};
  font-size: ${getFontSize('default')};
  font-weight: 400;
  text-transform: uppercase;
  padding: ${(props) => (props.size === ButtonSize.Small ? '0 10px' : '0 15px')};
  border-radius: 16px;
  height: ${(props) => (props.size === ButtonSize.Small ? '24px' : '32px')};

  ${getColorStyle}

  cursor: pointer;

  &:disabled {
    cursor: not-allowed;
  }
`;

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      level = ButtonLevel.Primary,
      ghost = false,
      disabled = false,
      size = ButtonSize.Default,
      ariaDescribedBy,
      ariaLabel,
      ariaLabelledBy,
      children,
      onClick,
      type = 'button',
      ...rest
    }: ButtonProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    useShortcut(Key.Enter, onClick);

    return (
      <Container
        level={level}
        ghost={ghost}
        disabled={disabled}
        size={size}
        aria-describedby={ariaDescribedBy}
        aria-disabled={disabled}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        ref={forwardedRef}
        role="button"
        type={type}
        {...rest}
      >
        {children}
      </Container>
    );
  }
);

export {Button, ButtonLevel, ButtonSize};
