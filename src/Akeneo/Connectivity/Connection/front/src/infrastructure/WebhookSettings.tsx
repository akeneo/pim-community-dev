import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {Index} from '../webhook/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

const WebhookSettings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Index />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));

export {WebhookSettings};
