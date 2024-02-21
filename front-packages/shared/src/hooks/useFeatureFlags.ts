import {useDependenciesContext} from './useDependenciesContext';
import {FeatureFlags} from '../DependenciesProvider.type';

const useFeatureFlags = (): FeatureFlags => {
  const {featureFlags} = useDependenciesContext();

  if (!featureFlags) {
    throw new Error('[DependenciesContext]: FeatureFlags has not been properly initiated');
  }

  return featureFlags;
};

export {useFeatureFlags};
