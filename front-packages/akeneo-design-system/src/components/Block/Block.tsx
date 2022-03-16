import React, {isValidElement, ReactNode, Ref, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {CloseIcon, IconProps} from '../../icons';
import {IconButton} from '../IconButton/IconButton';

type BlockProps = Override<
  Override<React.ButtonHTMLAttributes<HTMLButtonElement>, React.AnchorHTMLAttributes<HTMLAnchorElement>>,
  (
    | {
        /**
         * Define if the block is removable.
         */
        removable: false;
      }
    | {
        removable: boolean;

        /**
         * Function called when the user clicks on the remove button.
         */
        onRemove: () => void;

        /**
         * Accessibility text for the remove icon button.
         */
        removeLabel: string;
      }
  ) & {
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
    {
      removable,
      onRemove,
      removeLabel,
      ariaDescribedBy,
      ariaLabel,
      ariaLabelledBy,
      children,
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
        <div>
          {React.Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return React.cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}
        </div>
        <ActionsContainer>
          {removable && (
            <IconButton
              icon={<CloseIcon />}
              title={removeLabel}
              size="small"
              ghost="borderless"
              level="tertiary"
              onClick={handleRemove}
            />
          )}
        </ActionsContainer>
      </Container>
    );
  }
);

export {Block};
export type {BlockProps};
