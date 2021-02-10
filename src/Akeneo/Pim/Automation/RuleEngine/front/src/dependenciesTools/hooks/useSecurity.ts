import {useApplicationContext} from './useApplicationContext';
import {Security} from '../provider/applicationDependenciesProvider.type';

export const useSecurity = (): Security => {
  const {security} = useApplicationContext();

  if (security) {
    return security;
  }
  throw new Error('[ApplicationContext]: Security has not been properly initiated');
};
