import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {EditConnectionWebhook} from './EditConnectionWebhook';
import {ErrorBoundary} from './ErrorBoundary';

const Index = () => (
    <ErrorBoundary>
        <Switch>
            <Route path='/connections/:connectionCode/webhook'>
                <EditConnectionWebhook />
            </Route>
        </Switch>
    </ErrorBoundary>
);

export {Index};
