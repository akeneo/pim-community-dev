import React, {ComponentType, FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import {usePageContext} from "../../../../infrastructure/hooks";

interface TabContentWithPortalDecoratorProps {
  containerId: string;
  tabName: string
}

const TabContentWithPortalDecorator = <P extends object>(TabContentComponent:  ComponentType<P>): FunctionComponent<TabContentWithPortalDecoratorProps & P> => {
  return (props) => {
    const {containerId, tabName} = props;
    const portalContainer = document.getElementById(containerId);
    const {currentTab} = usePageContext();

    const isVisible = (currentTab: string) => {
      return currentTab === tabName;
    };

    return (
      <>
        {portalContainer && createPortal(
          <>
            {isVisible(currentTab) && (
              <TabContentComponent  {...props as P}/>
            )}
          </>,
        portalContainer
      )}
      </>
    );
  };
};

export default TabContentWithPortalDecorator;
