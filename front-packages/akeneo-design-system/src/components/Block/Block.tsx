import React, {cloneElement, isValidElement, ReactNode, Ref, useEffect, useRef, useState} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {ArrowDownIcon, ArrowUpIcon, IconProps} from '../../icons';
import {IconButton, IconButtonProps} from '../IconButton/IconButton';

type BlockProps = Override<
  Override<React.ButtonHTMLAttributes<HTMLButtonElement>, React.AnchorHTMLAttributes<HTMLAnchorElement>>,
  {
    /**
     * Add an action that will be displayed on the right of the block.
     */
    actions?: ReactNode;

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

    // /**
    //  * Block is open.
    //  */
    // open?: boolean;

    // /**
    //  * Block is collapsable.
    //  */
    // isCollapsable?: boolean;

    /**
     * Children of the button.
     */
    children?: ReactNode;
  } & (
    | {
        /**
         * Whether or not the Preview is open.
         */
        isOpen: boolean;

        /**
         * Label of the collapse button.
         */
        collapseButtonLabel: string;

        /**
         * Handler called when the collapse button is clicked.
         */
        onCollapse: (isOpen: boolean) => void;
      }
    | {
        isOpen?: undefined;
        collapseButtonLabel?: undefined;
        onCollapse?: undefined;
      }
  )
>;

const ANIMATION_DURATION = 100;

const ActionsContainer = styled.div`
  display: none;
  align-items: center;
`;

const BlockTitle = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 24px;
`;

const BlockContent = styled.div<
  {isCollapsable: boolean; $height: number; $overflow: string; shouldAnimate: boolean} & AkeneoThemedProps
>`
  overflow-wrap: break-word;
  white-space: break-spaces;
  color: ${getColor('grey', 140)};
  margin-top: ${({$height, isCollapsable}) => (0 === $height && isCollapsable ? 0 : 5)}px;
  ${({isCollapsable, $height, $overflow, shouldAnimate}) =>
    isCollapsable &&
    css`
      max-height: ${$height}px;
      overflow: ${$overflow};
      ${shouldAnimate &&
      css`
        transition: all ${ANIMATION_DURATION}ms ease-in-out;
        transition-property: max-height, margin-top;
      `}
    `}
`;

const Container = styled.div<AkeneoThemedProps>`
  box-sizing: border-box;
  padding: 10px 15px;
  border-style: solid;
  border-width: 1px;
  border-radius: 2px;
  display: flex;
  flex-direction: column;
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
    {actions, ariaDescribedBy, ariaLabel, ariaLabelledBy, isOpen, onCollapse, children, ...rest}: BlockProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    // isCollapsable = true;
    // const [isOpen, setIsOpen] = useState(open);
    // const handleCollapse = () => setIsOpen(!isOpen);

    const [contentHeight, setContentHeight] = useState<number>(0);
    const [shouldAnimate, setShouldAnimate] = useState<boolean>(false);
    const contentRef = useRef<HTMLDivElement>(null);

    const isCollapsable = undefined !== onCollapse && undefined !== isOpen;

    const handleCollapse = () => onCollapse?.(!isOpen);

    useEffect(() => {
      if (!isCollapsable) return;

      setContentHeight(contentHeight => {
        const scrollHeight = contentRef.current?.scrollHeight ?? 0;

        return 0 === scrollHeight ? contentHeight : scrollHeight;
      });

      const shouldAnimateTimeoutId = window.setTimeout(() => {
        setShouldAnimate(true);
      }, ANIMATION_DURATION);

      return () => {
        window.clearTimeout(shouldAnimateTimeoutId);
      };
    }, [children]);

    return (
      <Container
        aria-describedby={ariaDescribedBy}
        aria-label={ariaLabel}
        aria-labelledby={ariaLabelledBy}
        ref={forwardedRef}
        {...rest}
      >
        <BlockTitle>
          {React.Children.map(children, child => {
            if (isValidElement<IconProps>(child)) {
              return React.cloneElement(child, {size: child.props.size ?? 18});
            }

            return child;
          })}

          <ActionsContainer>
            {isCollapsable ? (
              <IconButton
                icon={isOpen ? <ArrowUpIcon /> : <ArrowDownIcon />}
                title="Collapse"
                level="tertiary"
                ghost="borderless"
                size="small"
                onClick={handleCollapse}
              />
            ) : null}
            {actions}
          </ActionsContainer>
        </BlockTitle>
        <BlockContent
          ref={contentRef}
          isCollapsable={isCollapsable}
          $overflow={shouldAnimate || !isOpen ? 'hidden' : 'inherit'}
          $height={true === isOpen ? contentHeight : 0}
          shouldAnimate={shouldAnimate}
          aria-hidden={!isOpen}
        >
          <div>Example content</div>
        </BlockContent>
      </Container>
    );
  }
);

export {Block};
export type {BlockProps};
