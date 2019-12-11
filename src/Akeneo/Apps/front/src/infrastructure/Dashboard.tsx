import {default as React, StrictMode, useReducer} from 'react';
import {DashboardStateContext} from '../application/dashboard/dashboard-state-context';
import {Index} from '../application/dashboard/pages/Index';
import {reducer} from '../application/dashboard/reducers/apps-reducer';
import {withContexts} from './with-contexts';

export const Dashboard = withContexts(() => {
    const [app, dispatch] = useReducer(reducer, {});

    return (
        <StrictMode>
            <DashboardStateContext.Provider value={[app, dispatch]}>
                <Index />
            </DashboardStateContext.Provider>
        </StrictMode>
    );
});
