import {toggleSidebar, setUpTabs} from 'akeneoassetmanager/application/event/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';

export const setUpSidebar = (sidebarIdentifier: string) => async (dispatch: any): Promise<void> => {
  const tabs: Tab[] = sidebarProvider.getTabs(sidebarIdentifier);
  dispatch(toggleSidebar(false));
  dispatch(setUpTabs(tabs));
};
