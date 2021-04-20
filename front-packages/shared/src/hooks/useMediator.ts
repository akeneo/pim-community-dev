import {useDependenciesContext} from './useDependenciesContext';
import {Mediator} from '../DependenciesProvider.type';

const useMediator = (): Mediator => {
  const {mediator} = useDependenciesContext();

  if (!mediator) {
    throw new Error('[DependenciesContext]: Mediator has not been properly initiated');
  }

  return mediator;
};

export {useMediator};
