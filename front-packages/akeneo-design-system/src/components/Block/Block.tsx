import React, {isValidElement, ReactNode, Ref, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {CloseIcon, IconProps} from '../../icons';

type BlockProps = Override<
  React.ButtonHTMLAttributes<HTMLButtonElement> & React.AnchorHTMLAttributes<HTMLAnchorElement>,
  {
    /**
     * Define if the block is removable.
     */
    removable?: boolean;

    /**
     * Function called when the user clicks on the remove button.
     */
    onRemove?: () => void;

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

const getColorStyle = () => {
  return css`
    background-color: ${getColor('white')};
    border-color: ${getColor('grey', 80)};
    color: ${getColor('grey', 140)};
    
    &:hover {
      background-color: ${getColor('grey', 20)};
    }
  `;
};

const Container = styled.div<
  {
    disabled: boolean;
  } & AkeneoThemedProps
>`
  padding: 15px;
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

  ${getColorStyle}
`;

const ContentContainer = styled.div``;

const ActionsContainer = styled.div``;

/**
 * TODO
 */
const Block = React.forwardRef<HTMLButtonElement, BlockProps>(
  (
    {
      removable = false,
      onRemove,
      ariaDescribedBy,
      ariaLabel,
      ariaLabelledBy,
      children,
      onClick,
      ...rest
    }: BlockProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    const handleRemove = (event: SyntheticEvent) => {
      event.preventDefault();
      if (onRemove) {
        onRemove();
      }
    };

    return (
      <Container
        aria-describedby={ariaDescribedBy}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        ref={forwardedRef}
        {...rest}
      >
        <ContentContainer>
          {React.Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return React.cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}
        </ContentContainer>
        <ActionsContainer>
          {removable && <CloseIcon size={18} onClick={handleRemove}/>}
        </ActionsContainer>
      </Container>
    );
  }
);

export {Block};
export type {BlockProps};
