import {toggleSidebar, setUpTabs} from 'akeneoreferenceentity/application/event/sidebar';
import sidebarProvider from 'akeneoreferenceentity/application/configuration/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';

export const setUpSidebar = (sidebarIdentifier: string) => async (dispatch: any): Promise<void> => {
  const tabs: Tab[] = sidebarProvider.getTabs(sidebarIdentifier);
  dispatch(toggleSidebar(false));
  dispatch(setUpTabs(tabs));
};
