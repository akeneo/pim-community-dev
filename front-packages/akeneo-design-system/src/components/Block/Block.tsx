import React, {cloneElement, isValidElement, ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {IconProps} from '../../icons';
import {IconButton, IconButtonProps} from '../IconButton/IconButton';

type BlockProps = Override<
  Override<React.ButtonHTMLAttributes<HTMLButtonElement>, React.AnchorHTMLAttributes<HTMLAnchorElement>>,
  {
    /**
     * Add an action that will be displayed on the right of the block.
     */
    action?: ReactNode;

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
    children?: ReactNode;
  }
>;

const ActionsContainer = styled.div`
  display: none;
  align-items: center;
`;

const Container = styled.div<AkeneoThemedProps>`
  box-sizing: border-box;
  padding: 0 20px;
  border-style: solid;
  border-width: 1px;
  border-radius: 2px;
  height: 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: inherit;
  font-size: ${getFontSize('default')};
  font-weight: 400;

  background-color: ${getColor('white')};
  border-color: ${getColor('grey', 80)};
  color: ${getColor('grey', 140)};

  &:hover {
    background-color: ${getColor('grey', 20)};
    ${ActionsContainer} {
      display: flex;
    }
  }
`;

const Block = React.forwardRef<HTMLButtonElement, BlockProps>(
  (
    {action, ariaDescribedBy, ariaLabel, ariaLabelledBy, children, ...rest}: BlockProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    return (
      <Container
        aria-describedby={ariaDescribedBy}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        ref={forwardedRef}
        {...rest}
      >
        <div>
          {React.Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return React.cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}
        </div>
        <ActionsContainer>
          {isValidElement<IconButtonProps>(action) && action.type === IconButton
            ? cloneElement(action, {
                level: 'tertiary',
                ghost: 'borderless',
                size: 'small',
              })
            : action}
        </ActionsContainer>
      </Container>
    );
  }
);

export {Block};
export type {BlockProps};
