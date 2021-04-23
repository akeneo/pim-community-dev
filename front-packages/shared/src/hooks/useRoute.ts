import {useMemo} from 'react';
import {useRouter} from './useRouter';

const useRoute = (route: string, parameters?: {[param: string]: string}) => {
  const {generate} = useRouter();

  return useMemo(() => generate(route, parameters), [generate, route, parameters]);
};

export {useRoute};
