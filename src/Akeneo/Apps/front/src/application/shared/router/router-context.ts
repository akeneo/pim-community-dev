import * as React from 'react';
import {Router} from './router.interface';

export const RouterContext = React.createContext<Router>({
    generate: (route: string) => route,
    redirect: () => undefined,
});
