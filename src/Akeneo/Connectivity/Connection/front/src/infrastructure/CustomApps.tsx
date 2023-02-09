import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {TestAppCreatePage} from '../connect/pages/TestAppCreatePage';
import {DeleteTestAppPromptPage} from '../connect/pages/DeleteTestAppPromptPage';

export const CustomApps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/custom-apps/create'>
                        <TestAppCreatePage />
                    </Route>
                    <Route path='/connect/custom-apps/:customAppId/delete'>
                        <DeleteTestAppPromptPage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
