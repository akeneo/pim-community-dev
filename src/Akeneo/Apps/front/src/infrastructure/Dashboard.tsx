import {default as React, StrictMode, useReducer} from 'react';
import {DashboardStateContext} from '../application/dashboard/dashboard-state-context';
import {Index} from '../application/dashboard/pages/Index';
import {reducer, initialState} from '../application/dashboard/reducers/dashboard-reducer';
import {withContexts} from './with-contexts';

export const Dashboard = withContexts(() => {
    const [app, dispatch] = useReducer(reducer, initialState);

    return (
        <StrictMode>
            <DashboardStateContext.Provider value={[app, dispatch]}>
                <Index />
            </DashboardStateContext.Provider>
        </StrictMode>
    );
});
