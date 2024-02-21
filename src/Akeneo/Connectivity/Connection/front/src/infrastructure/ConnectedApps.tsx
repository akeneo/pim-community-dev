import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {ConnectedAppsListPage} from '../connect/pages/ConnectedAppsListPage';
import {ConnectedAppPage} from '../connect/pages/ConnectedAppPage';
import {ConnectedAppDeletePage} from '../connect/pages/ConnectedAppDeletePage';
import {OpenAppPage} from '../connect/pages/OpenAppPage';
import {QueryClientProvider, QueryClient} from 'react-query';
import {ConnectedAppCatalogPage} from '../connect/pages/ConnectedAppCatalogPage';
import {RegenerateSecretPage} from '../connect/pages/RegenerateSecretPage';

const client = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 10 * 1000, // 10s
            cacheTime: 5 * 60 * 1000, // 5m
        },
    },
});

export const ConnectedApps = withDependencies(() => (
    <StrictMode>
        <QueryClientProvider client={client}>
            <AkeneoThemeProvider>
                <Router>
                    <Switch>
                        <Route path='/connect/connected-apps/:connectionCode/regenerate-secret'>
                            <RegenerateSecretPage />
                        </Route>
                        <Route path='/connect/connected-apps/:connectionCode/catalogs/:catalogId'>
                            <ConnectedAppCatalogPage />
                        </Route>
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
        </QueryClientProvider>
    </StrictMode>
));
