import {useDependenciesContext} from './useDependenciesContext';
import {AppcuesAnalytics} from '../DependenciesProvider.type';

const useAppcuesAnalytics = (): AppcuesAnalytics => {
  const {appcuesAnalytics} = useDependenciesContext();

  if (!appcuesAnalytics) {
    throw new Error('[DependenciesContext]: Appcues Analytics has not been properly initiated');
  }

  return appcuesAnalytics;
};

export {useAppcuesAnalytics};
