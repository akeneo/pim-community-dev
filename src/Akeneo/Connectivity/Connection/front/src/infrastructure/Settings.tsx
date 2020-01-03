import React, {StrictMode} from 'react';
import {Index} from '../settings/pages/Index';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';

export const Settings = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <Index />
        </AkeneoThemeProvider>
    </StrictMode>
));
