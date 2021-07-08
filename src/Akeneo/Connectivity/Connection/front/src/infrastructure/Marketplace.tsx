import React, {StrictMode} from 'react';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {MarketplaceWithoutUserProfile} from '../connect/pages/MarketplaceWithoutUserProfile';
import {Marketplace as MarketplacePage} from '../connect/pages/Marketplace';

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Switch>
                    <Route path='/connect/marketplace/profile'>
                        <MarketplaceWithoutUserProfile />
                    </Route>
                    <Route path='/connect/marketplace'>
                        <MarketplacePage />
                    </Route>
                </Switch>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
