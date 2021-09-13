import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {ConnectedAppsListPage} from "../connect/pages/ConnectedAppsListPage";

export const ConnectedApps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <ConnectedAppsListPage />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
