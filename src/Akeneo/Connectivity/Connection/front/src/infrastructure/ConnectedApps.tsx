import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {ConnectedAppsListPage} from '../connect/pages/ConnectedAppsListPage';
import {ConnectedAppPage} from '../connect/pages/ConnectedAppPage';
import {ConnectedAppDeletePage} from '../connect/pages/ConnectedAppDeletePage';
import {OpenAppPage} from '../connect/pages/OpenAppPage';

export const ConnectedApps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/connected-apps/:connectionCode/open'>
                        <OpenAppPage />
                    </Route>
                    <Route path='/connect/connected-apps/:connectionCode/delete'>
                        <ConnectedAppDeletePage />
                    </Route>
                    <Route path='/connect/connected-apps/:connectionCode'>
                        <ConnectedAppPage />
                    </Route>
                    <Route path='/connect/connected-apps'>
                        <ConnectedAppsListPage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
