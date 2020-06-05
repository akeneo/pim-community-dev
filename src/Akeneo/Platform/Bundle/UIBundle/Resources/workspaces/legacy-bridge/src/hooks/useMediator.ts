import {useDependenciesContext} from './useDependenciesContext';
import {Mediator} from '../provider';

const useMediator = (): Mediator => {
  const {mediator} = useDependenciesContext();

  if (!mediator) {
    throw new Error('[DependenciesContext]: Mediator has not been properly initiated');
  }

  return mediator;
};

export {useMediator};
