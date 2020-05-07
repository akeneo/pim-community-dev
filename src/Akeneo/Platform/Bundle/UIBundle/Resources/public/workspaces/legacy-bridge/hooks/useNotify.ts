import {useDependenciesContext} from './useDependenciesContext';
import {Notify} from '../provider';

const useNotify = (): Notify => {
  const {notify} = useDependenciesContext();

  if (notify) {
    return notify;
  }

  throw new Error('[ApplicationContext]: Notify has not been properly initiated');
};

export {useNotify};
