import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {
    MarketplaceWithoutUserProfile as MarketplaceWithoutUserProfilePage
} from '../connect/pages/MarketplaceWithoutUserProfile';

export const MarketplaceWithoutUserProfile = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <MarketplaceWithoutUserProfilePage/>
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
