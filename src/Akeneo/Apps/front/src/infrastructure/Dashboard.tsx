import {default as React, StrictMode, useReducer} from 'react';
import {AppsStateContext} from '../application/dashboard/app-state-context';
import {Index} from '../application/dashboard/pages/Index';
import {reducer} from '../application/dashboard/reducers/apps-reducer';
import {withContexts} from './with-contexts';

export const Dashboard = withContexts(() => {
    const [app, dispatch] = useReducer(reducer, {});

    return (
        <StrictMode>
            <AppsStateContext.Provider value={[app, dispatch]}>
                <Index />
            </AppsStateContext.Provider>
        </StrictMode>
    );
});
