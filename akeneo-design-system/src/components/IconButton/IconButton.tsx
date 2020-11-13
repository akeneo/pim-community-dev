import React, {Ref, ReactElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {IconProps} from '../../icons';

const Container = styled.button<{color: string} & AkeneoThemedProps>`
  background: none;
  border: none;
  padding: 0;
  display: inline-flex;
  color: ${({color}) => getColor(color, 100)};
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  opacity: ${({disabled}) => (disabled ? 0.6 : 1)};
`;

type IconButtonProps = {
  /**
   * The Icon to display.
   */
  icon: ReactElement<IconProps>;

  /**
   * The size of the Icon component.
   */
  size?: number;

  /**
   * The color of the Icon component.
   */
  color?: string;

  /**
   * Whether or not button is disabled.
   */
  disabled?: boolean;
} & React.ButtonHTMLAttributes<HTMLButtonElement>;

/**
 * The IconButton component is useful to have a clickable icon.
 */
const IconButton = React.forwardRef<HTMLButtonElement, IconButtonProps>(
  ({icon, size = 24, color = 'inherit', ...rest}: IconButtonProps, forwardedRef: Ref<HTMLButtonElement>) => {
    return (
      <Container ref={forwardedRef} color={color} {...rest}>
        {React.cloneElement(icon, {size})}
      </Container>
    );
  }
);

export {IconButton};
