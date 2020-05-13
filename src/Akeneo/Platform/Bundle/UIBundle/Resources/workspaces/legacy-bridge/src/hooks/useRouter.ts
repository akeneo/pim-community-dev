import {useDependenciesContext} from './useDependenciesContext';
import {Router} from '../provider';

const useRouter = (): Router => {
  const {router} = useDependenciesContext();

  if (!router) {
    throw new Error('[DependenciesContext]: Router has not been properly initiated');
  }

  return router;
};

export {useRouter};
