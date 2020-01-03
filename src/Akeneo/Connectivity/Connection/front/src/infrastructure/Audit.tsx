import React, {StrictMode} from 'react';
import {Index} from '../audit/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

export const Audit = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Index />
        </AkeneoThemeProvider>
    </StrictMode>
));
