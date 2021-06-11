import {PanelCloseIcon, PanelOpenIcon} from 'icons';
import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from 'theme';

const Panel = styled.div<{isOpen: boolean} & AkeneoThemedProps>`
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  display: flex;
  flex-direction: column;
  height: 100%;
  max-width: ${({isOpen}) => (isOpen ? '280px' : '40px')};
`;

const PanelContent = styled.div`
  flex-grow: 1;
  padding: 20px;
`;

const CloseButton = styled.button`
  align-items: center;
  background: none;
  border: none;
  border-top: 1px solid ${getColor('grey', 80)};
  cursor: pointer;
  display: flex;
  height: 54px;
  margin: 0 20px;
  padding: 0;
`;

const CloseIcon = styled(PanelCloseIcon)`
  color: ${getColor('grey', 100)};
  width: 15px;
`;

const OpenButton = styled.button`
  align-items: center;
  background: none;
  border: none;
  border-top: 1px solid ${getColor('grey', 80)};
  cursor: pointer;
  display: flex;
  height: 54px;
  justify-content: center;
  margin: 0;
  padding: 0;
`;

const OpenIcon = styled(PanelOpenIcon)`
  color: ${getColor('grey', 100)};
  width: 15px;
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
   * Handler called when the collapse button is clicked.
   */
  onCollapse: (isOpen: boolean) => void;
};

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const SubNavigationPanel = React.forwardRef<HTMLDivElement, SubNavigationPanelProps>(
  (
    {children, isOpen = true, onCollapse, ...rest}: SubNavigationPanelProps,
    forwardedRef: React.Ref<HTMLDivElement>
  ) => {
    return (
      <Panel ref={forwardedRef} isOpen={isOpen} {...rest}>
        <PanelContent>{isOpen && children}</PanelContent>

        {isOpen ? (
          <CloseButton onClick={() => onCollapse(false)} title="Close">
            <CloseIcon />
          </CloseButton>
        ) : (
          <OpenButton onClick={() => onCollapse(true)} title="Open">
            <OpenIcon />
          </OpenButton>
        )}
      </Panel>
    );
  }
);

export {SubNavigationPanel};
