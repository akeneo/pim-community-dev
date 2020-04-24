import { useApplicationContext } from './useApplicationContext';
import { UserContext } from '../provider/applicationDependenciesProvider.type';

export const useUserContext = (): UserContext => {
  const { user } = useApplicationContext();
  if (user) {
    return user;
  }
  throw new Error(
    '[ApplicationContext]: User Context has not been properly initiated'
  );
};
