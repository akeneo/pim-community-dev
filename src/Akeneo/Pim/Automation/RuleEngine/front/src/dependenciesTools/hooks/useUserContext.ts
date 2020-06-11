import { useApplicationContext } from './useApplicationContext';
import { UserContext } from '../provider/applicationDependenciesProvider.type';
import { LocaleCode } from '../../models';

export const useUserContext = (): UserContext => {
  const { user } = useApplicationContext();
  if (user) {
    return user;
  }
  throw new Error(
    '[ApplicationContext]: User Context has not been properly initiated'
  );
};

export const useUserCatalogLocale = (): LocaleCode => {
  return useUserContext().get('catalogLocale');
};
