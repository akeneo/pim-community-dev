import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {EditConnectionWebhook} from './EditConnectionWebhook';
import {ErrorBoundary} from './ErrorBoundary';
import {RegenerateWebhookSecret} from './RegenerateWebhookSecret';

const Index = () => (
    <ErrorBoundary>
        <Switch>
            <Route path='/connections/:connectionCode/event-subscription/regenerate-secret'>
                <RegenerateWebhookSecret />
            </Route>
            <Route path='/connections/:connectionCode/event-subscription'>
                <EditConnectionWebhook />
            </Route>
        </Switch>
    </ErrorBoundary>
);

export {Index};
