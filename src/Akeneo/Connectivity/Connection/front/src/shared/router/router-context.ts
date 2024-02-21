import {createContext} from 'react';
import {Router} from './router.interface';

export const RouterContext = createContext<Router>({
    generate: (route, parameters) => route + (parameters ? '?' + new URLSearchParams(parameters).toString() : ''),
    redirect: () => undefined,
});
