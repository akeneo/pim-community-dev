import {useDependenciesContext} from './useDependenciesContext';
import {UserContext} from '../DependenciesProvider.type';

const useUserContext = (): UserContext => {
  const {user} = useDependenciesContext();

  if (!user) {
    throw new Error('[DependenciesContext]: UserContext has not been properly initiated');
  }

  return user;
};

export {useUserContext};
