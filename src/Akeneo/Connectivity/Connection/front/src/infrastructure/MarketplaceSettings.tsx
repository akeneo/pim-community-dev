import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {Marketplace} from '../connect/pages/Marketplace';

export const MarketplaceSettings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Marketplace />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
