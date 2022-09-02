import {useConfig} from './useConfig';

const useSidebarConfig = () => {
  const config = useConfig();
  if (!config) {
    throw new Error('ConfigContext has not been properly initiated');
  }

  return config.sidebar;
};

export {useSidebarConfig};
