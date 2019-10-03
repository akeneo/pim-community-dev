import * as React from 'react';
import {Router} from './router.interface';

export const RouterContext = React.createContext<Router>({
    generate: (route: string) => {
        console.log('Generate URL for route:', route);
        return route;
    },
    redirect: (fragment: string, options?: object) => console.log('Redirect to fragment:', fragment, options),
});
