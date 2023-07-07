import React, {
  ButtonHTMLAttributes,
  Children,
  cloneElement,
  isValidElement,
  ReactElement,
  ReactNode,
  Ref,
  SyntheticEvent,
} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import type {IconProps} from '../../icons/IconProps';

type BlockButtonProps = Override<
  ButtonHTMLAttributes<HTMLButtonElement>,
  {
    /**
     * Icon displayed on the right of the button.
     */
    icon: ReactElement<IconProps>;

    /**
     * Used when the user cannot proceed or until an input is collected.
     */
    disabled?: boolean;

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
    children?: ReactNode;
  }
>;

const getColorStyle = ({disabled}: {disabled: boolean} & AkeneoThemedProps) => {
  if (disabled) {
    return css`
      border-color: ${getColor('grey', 100)};
      color: ${getColor('grey', 100)};
    `;
  }

  return css`
    background-color: ${getColor('white')};
    border-color: ${getColor('blue', 100)};
    color: ${getColor('blue', 100)};
  `;
};

const Container = styled.button<
  {
    disabled: boolean;
  } & AkeneoThemedProps
>`
  box-sizing: border-box;
  width: 100%;
  padding: 14px 20px;
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
  outline-style: none;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  white-space: nowrap;
  text-transform: uppercase;

  &:focus {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  ${getColorStyle}
`;

const ChildrenContainer = styled.div`
  display: flex;
  align-items: center;
  gap: 10px;
`;

const ActionsContainer = styled.div`
  display: flex;
  align-items: center;
`;

const BlockButton: React.FC<BlockButtonProps & {ref?: React.Ref<HTMLButtonElement>}> = React.forwardRef<HTMLButtonElement, BlockButtonProps>(
  (
    {icon, disabled = false, ariaDescribedBy, ariaLabel, ariaLabelledBy, children, onClick, ...rest}: BlockButtonProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    const handleAction = (event: SyntheticEvent) => {
      if (disabled || undefined === onClick) return;

      onClick(event);
    };

    return (
      <Container
        disabled={disabled}
        aria-describedby={ariaDescribedBy}
        aria-disabled={disabled}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        ref={forwardedRef}
        role="button"
        onClick={handleAction}
        {...rest}
      >
        <ChildrenContainer>
          {Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}
        </ChildrenContainer>
        <ActionsContainer>
          {isValidElement<IconProps>(icon) && cloneElement(icon, {size: icon.props.size ?? 18})}
        </ActionsContainer>
      </Container>
    );
  }
);

export {BlockButton};
export type {BlockButtonProps};
