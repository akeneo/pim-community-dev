import {useDependenciesContext} from './useDependenciesContext';
import {Analytics} from '../DependenciesProvider.type';

const useAnalytics = (): Analytics => {
  const {analytics} = useDependenciesContext();

  if (!analytics) {
    throw new Error('[DependenciesContext]: Analytics has not been properly initiated');
  }

  return analytics;
};

export {useAnalytics};
