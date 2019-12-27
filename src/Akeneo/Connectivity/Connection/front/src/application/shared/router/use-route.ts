import {useContext, useMemo} from 'react';
import {RouterContext} from './router-context';

export const useRoute = (route: string, parameters?: object) => {
    const {generate} = useContext(RouterContext);

    return useMemo(() => generate(route, parameters), [generate, route, parameters]);
};
