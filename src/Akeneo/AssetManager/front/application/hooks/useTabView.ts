import {useSidebarConfig} from './useSidebarConfig';
import {getView} from '../configuration/sidebar';

const useTabView = (sidebarIdentifier: string, tabCode: string) => {
  const sidebarConfig = useSidebarConfig();

  return getView(sidebarConfig, sidebarIdentifier, tabCode);
};

export {useTabView};
