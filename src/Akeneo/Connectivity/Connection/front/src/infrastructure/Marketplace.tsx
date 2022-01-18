import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {SelectUserProfilePage} from '../connect/pages/SelectUserProfilePage';
import {MarketplacePage} from '../connect/pages/MarketplacePage';
import {TestAppCreatePage} from '../connect/pages/TestAppCreatePage';
import {DeleteTestAppPromptPage} from '../connect/pages/DeleteTestAppPromptPage';

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/marketplace/test-apps/create'>
                        <TestAppCreatePage />
                    </Route>
                    <Route path='/connect/marketplace/test-apps/:testAppId/delete'>
                        <DeleteTestAppPromptPage />
                    </Route>
                    <Route path='/connect/marketplace/profile'>
                        <SelectUserProfilePage />
                    </Route>
                    <Route path='/connect/marketplace'>
                        <MarketplacePage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
