import React from 'react';
import {RouterContext} from './router/router-context';
import {useFetch} from './use-fetch';

export const useFetchFromRoute = <T, E>(route: string, parameters?: object) => {
    const {generate} = React.useContext(RouterContext);

    return useFetch<T, E>(generate(route, parameters));
};
