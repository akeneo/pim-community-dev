import * as React from 'react';

export interface SidebarLabel {
  default: React.SFC;
}

export interface Tab {
  code: string;
  label: string|SidebarLabel;
}

export interface SidebarState {
  isCollapsed?: boolean;
  tabs?: Tab[];
  currentTab?: string;
}
export default (
  state: SidebarState = {},
  action: {type: string; isCollapsed: boolean; tabs: Tab[]; currentTab: string}
): SidebarState => {
  switch (action.type) {
    case 'TOGGLE_SIDEBAR':
      state = {...state, isCollapsed: action.isCollapsed};
      break;
    case 'SETUP_SIDEBAR_TABS':
      state = {...state, tabs: action.tabs};
      break;
    case 'UPDATE_CURRENT_SIDEBAR_TAB':
      state = {...state, currentTab: action.currentTab};
      break;
    default:
      break;
  }

  return state;
};
