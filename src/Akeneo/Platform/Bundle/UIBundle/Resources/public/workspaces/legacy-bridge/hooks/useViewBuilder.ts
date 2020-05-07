import {useDependenciesContext} from './useDependenciesContext';
import {ViewBuilder} from '../provider';

const useViewBuilder = (): ViewBuilder => {
  const {viewBuilder} = useDependenciesContext();

  if (viewBuilder) {
    return viewBuilder;
  }

  throw new Error('[DependenciesContext]: ViewBuilder has not been properly initiated');
};

export {useViewBuilder};
