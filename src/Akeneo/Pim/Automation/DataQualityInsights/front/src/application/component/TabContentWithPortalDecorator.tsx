import React, {ComponentType, FC, useCallback} from 'react';
import {createPortal} from 'react-dom';

import PageContextHook from '../../infrastructure/hooks/PageContextHook';
import {PageContextState} from '../state/PageContextState';

interface TabContentWithPortalDecoratorProps {
  containerId: string;
  tabName: string;
}

const TabContentWithPortalDecorator = <P extends object, C extends PageContextState>(
  TabContentComponent: ComponentType<P>,
  usePageContextHook: PageContextHook<C>
): FC<TabContentWithPortalDecoratorProps & P> => {
  return props => {
    const {containerId, tabName} = props;
    const portalContainer = document.getElementById(containerId);
    const {currentTab} = usePageContextHook();

    const isVisible = useCallback(
      (currentTab: string | null) => {
        return currentTab === tabName;
      },
      [tabName]
    );

    return (
      <>
        {portalContainer &&
          createPortal(<>{isVisible(currentTab) && <TabContentComponent {...(props as P)} />}</>, portalContainer)}
      </>
    );
  };
};

export default TabContentWithPortalDecorator;
