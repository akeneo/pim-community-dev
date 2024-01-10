import React, {ForwardRefExoticComponent, PropsWithoutRef, RefAttributes, useLayoutEffect} from 'react';
import styled from 'styled-components';
import {PanelCloseIcon, PanelOpenIcon} from '../../../icons';
import {AkeneoThemedProps, getColor} from '../../../theme';

const Container = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  height: 100%;
  transition: width 0.3s linear;
  width: ${({isOpen}) => (isOpen ? '280px' : '40px')};
`;

const Content = styled.div<{isOpen: boolean; noPadding: boolean}>`
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  overflow-x: hidden;
  overflow-y: auto;
  opacity: ${({isOpen}) => (isOpen ? '1' : '0')};
  transition: ${({isOpen}) => (isOpen ? 'opacity 300ms linear 300ms' : 'none')};
  padding: ${({noPadding}) => (noPadding ? '0' : '30px')};
`;

const ToggleButton = styled.button<AkeneoThemedProps>`
  align-items: center;
  background: none;
  border: none;
  border-top: 1px solid ${getColor('grey', 80)};
  box-sizing: border-box;
  cursor: pointer;
  display: flex;
  flex: 0 0 auto;
  height: 54px;
  padding: 0;
  padding-left: 12.5px;

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
`;

const Collapsed = styled.div`
  padding: 10px 5px;
`;

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

  /**
   * Whether or not the panel should have padding.
   */
  noPadding?: boolean;
};

type SubNavigationPanelCompoundType = ForwardRefExoticComponent<
  PropsWithoutRef<SubNavigationPanelProps> & RefAttributes<HTMLDivElement>
> & {
  Collapsed?: any;
  Section?: any;
};

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const SubNavigationPanel: SubNavigationPanelCompoundType = React.forwardRef<HTMLDivElement, SubNavigationPanelProps>(
  (
    {
      children,
      isOpen = true,
      open,
      close,
      closeTitle = '',
      openTitle = '',
      noPadding = false,
      ...rest
    }: SubNavigationPanelProps,
    forwardedRef: React.Ref<HTMLDivElement>
  ) => {
    const collapsedElements: React.ReactNode[] = [];
    const contentElements: React.ReactNode[] = [];

    React.Children.forEach(children, child => {
      if (React.isValidElement(child) && child.type === Collapsed) {
        collapsedElements.push(child);
      } else {
        contentElements.push(child);
      }
    });

    const [isOpenTransition, setIsOpenTransition] = React.useState<boolean>(isOpen);
    useLayoutEffect(() => {
      setIsOpenTransition(isOpen);
    }, [isOpen]);

    return (
      <Container ref={forwardedRef} isOpen={isOpen} {...rest}>
        {!isOpen && collapsedElements}
        <Content isOpen={isOpenTransition} noPadding={noPadding}>
          {isOpen && contentElements}
        </Content>
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

SubNavigationPanel.displayName = 'SubNavigationPanel';
Collapsed.displayName = 'SubNavigationPanel.Collapsed';

SubNavigationPanel.Collapsed = Collapsed;

export {SubNavigationPanel};
