import React, {isValidElement, ReactNode, Ref, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {ArrowDownIcon, IconProps} from '../../icons';

type BlockButtonProps = Override<
  React.ButtonHTMLAttributes<HTMLButtonElement> & React.AnchorHTMLAttributes<HTMLAnchorElement>,
  {
    /**
     * Use when the user cannot proceed or until an input is collected.
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
  width: 100%;
  padding: 14px 20px;
  border-style: solid;
  border-width: 1px;
  border-radius: 2px;
  display: flex;
  justify-content: space-between;
  font-family: inherit;
  font-size: ${getFontSize('default')};
  font-weight: 400;
  outline-style: none;
  text-decoration: none;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  outline-style: none;
  text-decoration: none;

  &:focus {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  ${getColorStyle}
`;

const ChildrenContainer = styled.div`
  display: flex;
  align-items: center;
`;

const ActionsContainer = styled.div`
  display: flex;
  align-items: center;
`;

const BlockButton = React.forwardRef<HTMLButtonElement, BlockButtonProps>(
  (
    {disabled = false, ariaDescribedBy, ariaLabel, ariaLabelledBy, children, onClick, ...rest}: BlockButtonProps,
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
          {React.Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return React.cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}
        </ChildrenContainer>
        <ActionsContainer>
          <ArrowDownIcon size={18} />
        </ActionsContainer>
      </Container>
    );
  }
);

export {BlockButton};
export type {BlockButtonProps};
