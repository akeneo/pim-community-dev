import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {ConnectionMonitoring} from './ConnectionMonitoring';
import {ErrorBoundary} from './ErrorBoundary';

const Index = () => (
    <ErrorBoundary>
        <Switch>
            <Route path='/connections/:connectionCode/monitoring'>
                <ConnectionMonitoring />
            </Route>
        </Switch>
    </ErrorBoundary>
);

export {Index};
