import {useConfig} from './useConfig';

const useValueConfig = () => {
  const config = useConfig();
  if (!config) {
    throw new Error('ConfigContext has not been properly initiated');
  }

  return config.value;
};

export {useValueConfig};
