import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {Index} from '../webhook/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import store from '../webhook/store';
import {Provider} from 'react-redux';

const WebhookSettings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Provider store={store}>
                <Router>
                    <Index />
                </Router>
            </Provider>
        </AkeneoThemeProvider>
    </StrictMode>
));

export {WebhookSettings};
