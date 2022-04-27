import React, {StrictMode} from 'react';
import {HashRouter as Router, Redirect, Route, Switch} from 'react-router-dom';
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
                    <Redirect from='/connect/marketplace/test-apps/create' to='/connect/app-store/test-apps/create' />
                    <Route path='/connect/app-store/test-apps/create'>
                        <TestAppCreatePage />
                    </Route>
                    <Redirect
                        from='/connect/marketplace/test-apps/:testAppId/delete'
                        to='/connect/app-store/test-apps/:testAppId/delete'
                    />
                    <Route path='/connect/app-store/test-apps/:testAppId/delete'>
                        <DeleteTestAppPromptPage />
                    </Route>
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
    </StrictMode>
));
