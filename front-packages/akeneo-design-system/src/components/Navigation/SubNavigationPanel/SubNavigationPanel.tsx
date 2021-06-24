import React from 'react';
import styled from 'styled-components';
import {useBooleanState} from '../../../hooks';
import {PanelCloseIcon, PanelOpenIcon} from '../../../icons';
import {AkeneoThemedProps, getColor} from '../../../theme';

const Panel = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  display: flex;
  flex-direction: column;
  height: 100%;
  max-width: ${({isOpen}) => (isOpen ? '280px' : '40px')};
  transition: max-width 0.3s ease-in-out;
`;

const PanelContent = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  flex-grow: 1;
  opacity: ${({isOpen}) => (isOpen ? 1 : 0)};
  padding: 20px;
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

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
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
};

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const SubNavigationPanel = React.forwardRef<HTMLDivElement, SubNavigationPanelProps>(
  (
    {children, isOpen: defaultIsOpen = true, ...rest}: SubNavigationPanelProps,
    forwardedRef: React.Ref<HTMLDivElement>
  ) => {
    const [isOpen, open, close] = useBooleanState(defaultIsOpen);

    return (
      <Panel ref={forwardedRef} isOpen={isOpen} {...rest}>
        <PanelContent isOpen={isOpen}>{isOpen && children}</PanelContent>

        <ToggleButton isOpen={isOpen} onClick={() => (isOpen ? close() : open())} title={isOpen ? 'Close' : 'Open'}>
          {isOpen ? <PanelCloseIcon /> : <PanelOpenIcon />}
        </ToggleButton>
      </Panel>
    );
  }
);

export {SubNavigationPanel};
