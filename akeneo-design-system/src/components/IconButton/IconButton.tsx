import React, {Ref, ReactElement} from 'react';
import styled from 'styled-components';
import {IconProps} from '../../icons';
import {Button, ButtonProps, ButtonSize} from '../../components/Button/Button';
import {Override} from '../../shared';

const IconButtonContainer = styled(Button)<ButtonProps & {borderless: boolean}>`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  width: ${({size}) => (size === 'small' ? 24 : 32)}px;
  border-style: ${({borderless, ghost}) => (!borderless && ghost ? 'solid' : 'none')};
`;

const getIconSize = (size: ButtonSize): number => {
  switch (size) {
    case 'small':
      return 16;
    case 'default':
      return 20;
  }
};

type IconButtonProps = Override<
  Omit<ButtonProps, 'children'>,
  {
    /**
     * When an action does not require primary dominance on the page. The IconButton can also be borderless.
     */
    ghost?: boolean | 'borderless';

    /**
     * The Icon to display.
     */
    icon: ReactElement<IconProps>;

    /**
     * The title of the button.
     */
    title: string;
  }
>;

/**
 * The IconButton component is useful to have a clickable icon.
 */
const IconButton = React.forwardRef<HTMLButtonElement, IconButtonProps>(
  ({icon, size = 'default', ghost, ...rest}: IconButtonProps, forwardedRef: Ref<HTMLButtonElement>) => {
    return (
      <IconButtonContainer
        ref={forwardedRef}
        ghost={true === ghost || 'borderless' === ghost}
        borderless={'borderless' === ghost}
        size={size}
        {...rest}
      >
        {React.cloneElement(icon, {size: getIconSize(size)})}
      </IconButtonContainer>
    );
  }
);

export {IconButton};
export type {IconButtonProps};
