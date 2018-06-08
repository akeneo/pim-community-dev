import {toggleSidebar, setUpTabs, updateCurrentTab} from 'akeneoenrichedentity/application/event/sidebar';
import editTabsProvider from 'akeneoenrichedentity/application/configuration/edit-tabs';
import {Tab} from 'akeneoenrichedentity/application/reducer/sidebar';

export const setUpSidebar = () => async (dispatch: any): Promise<void> => {
  const tabs: Tab[] = editTabsProvider.getTabs();
  dispatch(toggleSidebar(false));
  dispatch(setUpTabs(tabs));
  dispatch(updateCurrentTab(editTabsProvider.getCurrentTab()));
};
