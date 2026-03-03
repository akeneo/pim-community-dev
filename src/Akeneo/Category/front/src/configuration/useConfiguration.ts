import {useContext} from 'react';
import {ConfigurationContext} from './ConfigurationProvider';

const useConfiguration = () => {
  const context = useContext(ConfigurationContext);
  if (undefined === context) {
    throw new Error('useConfiguration must be used within a ConfigurationProvider');
  }

  return context;
};

export {useConfiguration};
