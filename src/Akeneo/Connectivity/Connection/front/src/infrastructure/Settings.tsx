import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {Index} from '../settings/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

export const Settings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Index />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));
