import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {Marketplace as MarketplacePage} from '../connect/pages/Marketplace';

export const Marketplace = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <MarketplacePage />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
