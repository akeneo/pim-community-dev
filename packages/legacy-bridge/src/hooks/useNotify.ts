import {useDependenciesContext} from './useDependenciesContext';
import {Notify} from '../DependenciesProvider.type';

const useNotify = (): Notify => {
  const {notify} = useDependenciesContext();

  if (!notify) {
    throw new Error('[DependenciesContext]: Notify has not been properly initiated');
  }

  return notify;
};

export {useNotify};
