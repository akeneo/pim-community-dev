import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {ConnectionMonitoring} from './ConnectionMonitoring';
import {DashboardBusinessErrors} from './DashboardBusinessErrors';
import {ErrorBoundary} from './ErrorBoundary';

const Index = () => (
    <ErrorBoundary>
        <Switch>
            <Route path='/connection/dashboard/business-errors'>
                <DashboardBusinessErrors />
            </Route>
            <Route path='/connections/:connectionCode/monitoring'>
                <ConnectionMonitoring />
            </Route>
        </Switch>
    </ErrorBoundary>
);

export {Index};
