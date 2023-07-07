import React, {ForwardRefExoticComponent, PropsWithoutRef, ReactNode, RefAttributes} from 'react';
import styled, {css} from 'styled-components';
import {PanelCloseIcon} from '../../../icons/PanelCloseIcon';
import {PanelOpenIcon} from '../../../icons/PanelOpenIcon';
import {AkeneoThemedProps, getColor} from '../../../theme';

const collapsableStyles = css<{isOpen: boolean} & AkeneoThemedProps>`
  opacity: ${({isOpen}) => (isOpen ? 1 : 0)};
  transition: opacity 0.3s;
  transition-delay: 0.3s;

  position: relative;
  z-index: 1;
  ${({isOpen}) =>
    !isOpen &&
    css`
      position: absolute;
      z-index: 0;
    `}
`;
const Panel = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  display: flex;
  flex-direction: column;
  height: calc(100% - 54px);
  width: 100%;
  position: absolute;
  overflow-y: ${({isOpen}) => (isOpen ? 'auto' : 'hidden')};
  overflow-x: hidden;
`;

const PanelContent = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  box-sizing: border-box;
  flex-grow: 1;
  width: 100%;
  padding: ${({isOpen}) => (isOpen ? '30px' : '10px 5px')};
  ${collapsableStyles}
`;

const ToggleButton = styled.button<{isOpen: boolean} & AkeneoThemedProps>`
  align-items: center;
  background: none;
  border: none;
  border-top: 1px solid ${getColor('grey', 80)};
  cursor: pointer;
  height: 54px;
  margin: ${({isOpen}) => (isOpen ? '0 20px' : '0')};
  padding: ${({isOpen}) => (isOpen ? '0' : '0 12.5px')};
  transition: margin 0.3s ease-in-out, padding 0.3s ease-in-out;
  text-align: left;
  position: absolute;
  bottom: 0;
  width: ${({isOpen}) => (isOpen ? '240px' : '40px')};

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
`;

const Container = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  width: ${({isOpen}) => (isOpen ? '280px' : '40px')};
  transition: width 0.3s linear;
  position: relative;
  order: -10;
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  flex-shrink: 0;
  height: 100%;
  z-index: 802;
  overflow: hidden;
`;

const Collapsed = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  flex-grow: 1;
  padding: 10px 5px;
  ${collapsableStyles}
`;

Collapsed.displayName = 'Collapsed';
Collapsed.defaultProps = {
  isOpen: false,
};

type SubNavigationPanelProps = {
  /**
   * The content of the panel.
   */
  children?: React.ReactNode;

  /**
   * Whether or not the panel is open.
   */
  isOpen?: boolean;

  /**
   * Callback to open the sub navigation panel
   */
  open: () => void;

  /**
   * Callback to close the sub navigation panel
   */
  close: () => void;

  /**
   * Closing title to display for the ToggleButton
   */
  closeTitle?: string;

  /**
   * Opening title to display for the ToggleButton
   */
  openTitle?: string;
};

type SubNavigationPanelCompoundType = ForwardRefExoticComponent<
  PropsWithoutRef<SubNavigationPanelProps> & RefAttributes<HTMLDivElement>
> & {
  Collapsed?: any;
};

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const SubNavigationPanel: SubNavigationPanelCompoundType = React.forwardRef<HTMLDivElement, SubNavigationPanelProps>(
  (
    {children, isOpen: isOpen = true, open, close, closeTitle = '', openTitle = '', ...rest}: SubNavigationPanelProps,
    forwardedRef: React.Ref<HTMLDivElement>
  ) => {
    const contentChildren: ReactNode[] = [];
    let collapsedContent: ReactNode | null = null;
    React.Children.forEach(children, child => {
      if (React.isValidElement(child) && child.type === Collapsed) {
        collapsedContent = React.cloneElement(child as any, {isOpen: !isOpen});
        return;
      }
      contentChildren.push(child);
    });

    return (
      <Container ref={forwardedRef} isOpen={isOpen} {...rest}>
        <Panel isOpen={isOpen}>
          {collapsedContent}
          <PanelContent isOpen={isOpen}>{isOpen && contentChildren}</PanelContent>
        </Panel>
        <ToggleButton
          isOpen={isOpen}
          onClick={() => (isOpen ? close() : open())}
          title={isOpen ? closeTitle : openTitle}
          data-testid="open-subnavigation-button"
        >
          {isOpen ? <PanelCloseIcon /> : <PanelOpenIcon />}
        </ToggleButton>
      </Container>
    );
  }
);

SubNavigationPanel.Collapsed = Collapsed;

export {SubNavigationPanel};
