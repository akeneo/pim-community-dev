export interface Tab {
  code: string;
  label: string;
  panel: string;
}

export interface SidebarState {
  isCollapsed: boolean;
  tabs: Tab[];
  currentTab: string;
}
export default (
  state: SidebarState = {isCollapsed: false, tabs: [], currentTab: ''},
  action: {type: string; isCollapsed: boolean; tabs: Tab[]; currentTab: string}
): SidebarState => {
  switch (action.type) {
    case 'TOGGLE_SIDEBAR':
      state = {...state, isCollapsed: action.isCollapsed};
      break;
    case 'SETUP_TABS':
      state = {...state, tabs: action.tabs};
      break;
    case 'UPDATE_CURRENT_TAB':
      state = {...state, currentTab: action.currentTab};
      break;
    default:
      break;
  }

  return state;
};
