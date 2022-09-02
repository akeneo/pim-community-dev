import {useSecurity} from '@akeneo-pim-community/shared';
import {useSidebarConfig} from './useSidebarConfig';
import {getTabs} from '../configuration/sidebar';

const useSidebarTabs = (sidebarIdentifier: string) => {
  const sidebarConfig = useSidebarConfig();
  const securityContext = useSecurity();

  return getTabs(securityContext, sidebarConfig, sidebarIdentifier);
};

export {useSidebarTabs};
