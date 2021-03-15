import {useContext, useMemo} from 'react';
import {RouterContext} from './router-context';

export const useRoute = (route: string, parameters?: {[param: string]: string}) => {
    const {generate} = useContext(RouterContext);

    return useMemo(() => generate(route, parameters), [generate, route, parameters]);
};
