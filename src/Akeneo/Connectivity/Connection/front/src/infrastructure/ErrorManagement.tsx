import React, {StrictMode} from 'react';
import {HashRouter as Router} from 'react-router-dom';
import {Index} from '../error-management/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

const ErrorManagement = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Router>
                <Index />
            </Router>
        </AkeneoThemeProvider>
    </StrictMode>
));

export {ErrorManagement};
