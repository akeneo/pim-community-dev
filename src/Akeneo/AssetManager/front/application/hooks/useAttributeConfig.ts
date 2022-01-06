import {useConfig} from './useConfig';

const useAttributeConfig = () => {
  const config = useConfig();
  if (!config) {
    throw new Error('ConfigContext has not been properly initiated');
  }

  return config.attribute;
};

export {useAttributeConfig};
