import { useApplicationContext } from './useApplicationContext';

export const useUserContext = () => {
  const { user } = useApplicationContext();
  if (user) {
    return user;
  }
  throw new Error(
    '[ApplicationContext]: User Context has not been properly initiated'
  );
};
