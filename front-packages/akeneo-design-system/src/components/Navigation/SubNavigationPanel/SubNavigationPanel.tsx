import React from 'react';
import styled from 'styled-components';
import {PanelCloseIcon, PanelOpenIcon} from '../../../icons';
import {AkeneoThemedProps, getColor} from '../../../theme';

const Panel = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  display: flex;
  flex-direction: column;
  height: calc(100% - 54px);
  width: 100%;
  position: absolute;
  overflow-x: ${({isOpen}) => (isOpen ? 'auto' : 'none')};
`;

const PanelContent = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  flex-grow: 1;
  //opacity: ${({isOpen}) => (isOpen ? 1 : 0)};
  padding: ${({isOpen}) => (isOpen ? '30px' : '10px 5px')};
  transition: opacity 0.3s;
  transition-delay: 0.3s;
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
  transition: margin 0.3s ease-in-out;
  text-align: left;
  position: absolute;
  bottom: 0;
  width: ${({isOpen}) => (isOpen ? '240px' : '40px')};;

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
`;

const Container = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  width: ${({isOpen}) => (isOpen ? '280px' : '40px')};
  transition: width 0.3s ease-in-out;
  position: relative;
  order: -10;
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  flex-shrink: 0;
  height: 100%;
  z-index: 802;
  overflow: hidden;
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
};

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const SubNavigationPanel = React.forwardRef<HTMLDivElement, SubNavigationPanelProps>(
  (
    {children, isOpen: isOpen = true, open, close, ...rest}: SubNavigationPanelProps,
    forwardedRef: React.Ref<HTMLDivElement>
  ) => {

    return (
      <Container isOpen={isOpen}>
        <Panel ref={forwardedRef} isOpen={isOpen} {...rest}>
          <PanelContent isOpen={isOpen}>{children}</PanelContent>
        </Panel>
        <ToggleButton
          isOpen={isOpen}
          onClick={() => (isOpen ? close() : open())}
          title={isOpen ? 'Close' : 'Open'}
          data-testid='open-subnavigation-button'
        >
          {isOpen ? <PanelCloseIcon /> : <PanelOpenIcon />}
        </ToggleButton>
      </Container>
    );
  }
);

export {SubNavigationPanel};
