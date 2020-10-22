import {useDependenciesContext} from './useDependenciesContext';
import {UserContext} from '../provider';

const useUserContext = (): UserContext => {
  const {user} = useDependenciesContext();

  if (!user) {
    throw new Error('[DependenciesContext]: User Context has not been properly initiated');
  }

  return user;
};

export {useUserContext};
