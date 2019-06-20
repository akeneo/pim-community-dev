import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';

export const toggleSidebar = (isCollapsed: boolean) => {
  return {type: 'TOGGLE_SIDEBAR', isCollapsed};
};

export const setUpTabs = (tabs: Tab[]) => {
  return {type: 'SETUP_SIDEBAR_TABS', tabs};
};

export const updateCurrentTab = (tabCode: string) => {
  return {type: 'UPDATE_CURRENT_SIDEBAR_TAB', currentTab: tabCode};
};
