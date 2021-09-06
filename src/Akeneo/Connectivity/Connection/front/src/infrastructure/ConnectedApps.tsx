import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {ConnectedAppPage} from "../connect/pages/ConnectedAppPage";

export const ConnectedApps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <ConnectedAppPage />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
