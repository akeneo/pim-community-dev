import {useSidebarConfig} from './useSidebarConfig';
import {getTabs} from '../configuration/sidebar';

const useSidebarTabs = (sidebarIdentifier: string) => {
  const sidebarConfig = useSidebarConfig();

  return getTabs(sidebarConfig, sidebarIdentifier);
};

export {useSidebarTabs};
