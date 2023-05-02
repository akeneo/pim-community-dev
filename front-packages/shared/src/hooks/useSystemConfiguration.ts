import {useDependenciesContext} from './useDependenciesContext';
import {SystemConfiguration} from '../DependenciesProvider.type';

const useSystemConfiguration = (): SystemConfiguration => {
  const {systemConfiguration} = useDependenciesContext();

  if (!systemConfiguration) {
    throw new Error('[DependenciesContext]: SystemConfiguration has not been properly initiated');
  }

  return systemConfiguration;
};

export {useSystemConfiguration};
