import {createContext} from 'react';
import {Router} from './router.interface';

export const RouterContext = createContext<Router>({
    generate: (route: string) => route,
    redirect: () => undefined,
});
