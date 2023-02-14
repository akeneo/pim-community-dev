import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {QueryClientProvider, QueryClient} from 'react-query';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {CreateCustomAppPage} from '../connect/pages/CreateCustomAppPage';
import {DeleteCustomAppPromptPage} from '../connect/pages/DeleteCustomAppPromptPage';

const client = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 10 * 1000, // 10s
            cacheTime: 5 * 60 * 1000, // 5m
        },
    },
});

export const CustomApps = withDependencies(() => (
    <StrictMode>
        <QueryClientProvider client={client}>
            <AkeneoThemeProvider>
                <Router>
                    <Switch>
                        <Route path='/connect/custom-apps/create'>
                            <CreateCustomAppPage />
                        </Route>
                        <Route path='/connect/custom-apps/:customAppId/delete'>
                            <DeleteCustomAppPromptPage />
                        </Route>
                    </Switch>
                </Router>
            </AkeneoThemeProvider>
        </QueryClientProvider>
    </StrictMode>
));
