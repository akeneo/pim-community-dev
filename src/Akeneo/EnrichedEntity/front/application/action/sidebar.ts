import {toggleSidebar, setUpTabs} from 'akeneoenrichedentity/application/event/sidebar';
import editTabsProvider from 'akeneoenrichedentity/application/configuration/sidebar';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

export const setUpSidebar = (sidebarIdentifier: string) => async (dispatch: any): Promise<void> => {
  const tabs: Tab[] = editTabsProvider.getTabs(sidebarIdentifier);
  dispatch(toggleSidebar(false));
  dispatch(setUpTabs(tabs));
};
