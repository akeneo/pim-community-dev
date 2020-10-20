import React, {isValidElement, ReactNode, Ref, RefAttributes, SyntheticEvent, useRef} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColorForLevel, getFontSize, Level} from '../../theme';
import {Override} from '../../shared';
import {Link, LinkProps} from 'components';

type ButtonSize = 'small' | 'default';

type ButtonProps = Override<
  React.ButtonHTMLAttributes<HTMLButtonElement>,
  {
    /**
     * Level of the button defining it's color and outline.
     * Possible values are: primary, secondary, tertiary, danger and ghost.
     */
    level?: Level;

    /**
     * When an action does not require primary dominance on the page.
     */
    ghost?: boolean;

    /**
     * Use when the user cannot proceed or until an input is collected.
     */
    disabled?: boolean;

    /**
     * Define the size of a button.
     */
    size?: ButtonSize;

    /**
     * Function called when the user clicks on the button or hit enter when focused.
     */
    onClick?: (event: SyntheticEvent) => void;

    /**
     * Accessibility label to describe shortly the button.
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
     * Children of the button.
     */
    children: ReactNode;
  }
>;

const getColorStyle = (props: {level: Level; ghost: boolean; disabled: boolean} & AkeneoThemedProps) => {
  const {level, ghost, disabled} = props;

  if (ghost) {
    return css`
      color: ${getColorForLevel(level, disabled ? 80 : 120)};
      background-color: white;
      border-color: ${getColorForLevel(level, disabled ? 60 : 100)};

      &:hover:not([disabled]) {
        color: ${getColorForLevel(level, 140)};
        background-color: ${getColorForLevel(level, 20)};
        border-color: ${getColorForLevel(level, 120)};
      }

      &:active:not([disabled]) {
        color: ${getColorForLevel(level, 140)};
        border-color: ${getColorForLevel(level, 140)};
      }
    `;
  }

  return css`
    color: white;
    background-color: ${getColorForLevel(level, disabled ? 40 : 100)};

    &:hover:not([disabled]) {
      background-color: ${getColorForLevel(level, 120)};
    }

    &:active:not([disabled]) {
      background-color: ${getColorForLevel(level, 140)};
    }
  `;
};

const Container = styled.button<
  {
    level: Level;
    ghost: boolean;
    disabled: boolean;
    size: ButtonSize;
  } & AkeneoThemedProps
>`
  border-width: 1px;
  border-style: ${props => (props.ghost ? 'solid' : 'none')};
  font-size: ${getFontSize('default')};
  font-weight: 400;
  text-transform: uppercase;
  padding: ${props => (props.size === 'small' ? '0 10px' : '0 15px')};
  border-radius: 16px;
  height: ${props => (props.size === 'small' ? '24px' : '32px')};

  ${getColorStyle}

  cursor: pointer;

  &:disabled {
    cursor: not-allowed;
  }

  a,
  a:hover {
    color: inherit;
    text-decoration: none;
    cursor: inherit;
  }
`;

/**
 * TODO throw if using <a>
 * Buttons express what action will occur when the users clicks.
 * Buttons are used to initialize an action, either in the background or foreground of an experience.
 */
const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      level = 'primary',
      ghost = false,
      disabled = false,
      size = 'default',
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
    const linkRef = useRef<HTMLAnchorElement>(null);

    const mappedChildren = React.Children.map(children, child => {
      if (isValidElement<LinkProps & RefAttributes<HTMLAnchorElement>>(child) && child.type === Link) {
        if (undefined !== onClick) {
          throw new Error('Button can not have both `onClick` and a Link as child');
        }

        if (1 < React.Children.count(children)) {
          throw new Error('Link can only be the single child of a Button');
        }

        return React.cloneElement(child, {ref: linkRef, disabled});
      } else {
        return child;
      }
    });

    const handleAction = (event: SyntheticEvent) => {
      if (disabled) return;

      if (null !== linkRef.current) {
        linkRef.current.click();
      } else {
        undefined !== onClick && onClick(event);
      }
    };

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
        onClick={handleAction}
        {...rest}
      >
        {mappedChildren}
      </Container>
    );
  }
);

export {Button};
