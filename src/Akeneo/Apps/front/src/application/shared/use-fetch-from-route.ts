import {useContext, useMemo} from 'react';
import {RouterContext} from './router/router-context';
import {useFetch} from './use-fetch';

export const useFetchFromRoute = <T, E>(route: string, parameters?: object) => {
    const {generate} = useContext(RouterContext);

    const url = useMemo(() => generate(route, parameters), [generate, route, parameters]);

    return useFetch<T, E>(url);
};
