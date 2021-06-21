import React from 'react';
import styled from 'styled-components';
import {PanelCloseIcon, PanelOpenIcon} from '../../../icons';
import {Override} from '../../../shared';
import {getColor} from '../../../theme';
import {Collapse} from './Collapse/Collapse';

const ContainerOpened = styled.div`
  background-color: ${getColor('grey', 20)};
  border-right: 1px solid ${getColor('grey', 80)};
  display: flex;
  flex-direction: column;
  height: 100%;
  max-width: 280px;
`;

const ContainerClosed = styled(ContainerOpened)`
  max-width: 40px;
`;

const ContentOpened = styled.div`
  flex-grow: 1;
  padding: 20px;
`;

const ContentClosed = styled(ContentOpened)`
  padding: 0;
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
  outline: none;

  :focus:not(:active) {
    box-shadow: inset 0 0 0 2px ${getColor('blue', 40)};
  }

  svg {
    color: ${getColor('grey', 100)};
    width: 15px;
  }
`;

const CloseButton = styled(OpenButton)`
  justify-content: left;
  margin: 0 20px;
`;

type Props = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The content of the panel
     */
    children?: React.ReactNode;

    /**
     * Whether or not the panel is open
     */
    isOpen?: boolean;

    /**
     * Handler called when the collapse button is clicked
     */
    onCollapse: (isOpen: boolean) => void;
  }
>;

/**
 * SubNavigationPanel is used to display a collapsable panel.
 */
const Component = React.forwardRef<HTMLDivElement, Props>(
  ({children, isOpen = true, onCollapse, ...rest}, forwardedRef: React.Ref<HTMLDivElement>) => {
    const collapseContent: React.ReactElement[] = [];
    const content = React.Children.toArray(children).map(child => {
      if (React.isValidElement(child) && child.type === Collapse) {
        collapseContent.push(child);

        return null;
      }

      return child;
    });

    if (!isOpen) {
      return (
        <ContainerClosed ref={forwardedRef} {...rest}>
          <ContentClosed>{collapseContent}</ContentClosed>
          <OpenButton onClick={() => onCollapse(true)} title="Open">
            <PanelOpenIcon />
          </OpenButton>
        </ContainerClosed>
      );
    }

    return (
      <ContainerOpened ref={forwardedRef} {...rest}>
        <ContentOpened>{content}</ContentOpened>
        <CloseButton onClick={() => onCollapse(false)} title="Close">
          <PanelCloseIcon />
        </CloseButton>
      </ContainerOpened>
    );
  }
);

Component.displayName = 'SubNavigationPanel';

Collapse.displayName = 'SubNavigationPanel.Collapse';

export const SubNavigationPanel = Object.assign(Component, {Collapse});
