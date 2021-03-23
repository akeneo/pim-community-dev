import React from 'react';
import {Route, Switch} from 'react-router-dom';
import {EditConnectionWebhook} from './EditConnectionWebhook';
import {ErrorBoundary} from './ErrorBoundary';
import {EventLogs} from './EventLogs';
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
            <Route path='/connections/:connectionCode/event-logs'>
                <EventLogs />
            </Route>
        </Switch>
    </ErrorBoundary>
);

export {Index};
