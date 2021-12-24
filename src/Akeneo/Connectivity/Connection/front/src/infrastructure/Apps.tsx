import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AppActivatePage} from '../connect/pages/AppActivatePage';
import {AppAuthenticatePage} from '../connect/pages/AppAuthenticatePage';
import {AppAuthorizePage} from '../connect/pages/AppAuthorizePage';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

export const Apps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/apps/activate'>
                        <AppActivatePage />
                    </Route>
                    <Route path='/connect/apps/authorize'>
                        <AppAuthorizePage />
                    </Route>
                    <Route path='/connect/apps/authenticate'>
                        <AppAuthenticatePage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
