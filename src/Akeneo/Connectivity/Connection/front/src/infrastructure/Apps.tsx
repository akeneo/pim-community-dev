import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {AppActivatePage} from '../connect/pages/AppActivatePage';
import {AppAuthorizePage} from "../connect/pages/AppAuthorizePage";

export const Apps = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/apps/activate'>
                        <AppActivatePage />
                    </Route>
                    <Route path='/connect/apps/authorize'>
                        <AppAuthorizePage/>
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
