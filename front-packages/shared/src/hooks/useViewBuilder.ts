import {useDependenciesContext} from './useDependenciesContext';
import {ViewBuilder} from '../DependenciesProvider.type';

const useViewBuilder = (): ViewBuilder => {
  const {viewBuilder} = useDependenciesContext();

  if (!viewBuilder) {
    throw new Error('[DependenciesContext]: ViewBuilder has not been properly initiated');
  }

  return viewBuilder;
};

export {useViewBuilder};
