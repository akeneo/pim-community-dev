import React, {StrictMode} from 'react';
import {HashRouter as Router, Redirect, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {SelectUserProfilePage} from '../connect/pages/SelectUserProfilePage';
import {MarketplacePage} from '../connect/pages/MarketplacePage';
import {QueryClient, QueryClientProvider} from 'react-query';

const client = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 10 * 1000, // 10s
            cacheTime: 5 * 60 * 1000, // 5m
        },
    },
});

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <QueryClientProvider client={client}>
            <AkeneoThemeProvider>
                <Router>
                    <Switch>
                        <Redirect from='/connect/marketplace/profile' to='/connect/app-store/profile' />
                        <Route path='/connect/app-store/profile'>
                            <SelectUserProfilePage />
                        </Route>
                        <Redirect from='/connect/marketplace' to='/connect/app-store' />
                        <Route path='/connect/app-store'>
                            <MarketplacePage />
                        </Route>
                    </Switch>
                </Router>
            </AkeneoThemeProvider>
        </QueryClientProvider>
    </StrictMode>
));
