import {useDependenciesContext} from './useDependenciesContext';
import {UserContext} from '../provider';

const useUserContext = (): UserContext => {
  const {user} = useDependenciesContext();

  if (user) {
    return user;
  }

  throw new Error('[ApplicationContext]: User Context has not been properly initiated');
};

export {useUserContext};
