import React, {StrictMode} from 'react';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {RedirectConnectionDashboardToConnectMenu as RedirectConnectionDashboardToConnectMenuPage} from '../connect/pages/RedirectConnectionDashboardToConnectMenu';

export const RedirectConnectionDashboardToConnectMenu = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <RedirectConnectionDashboardToConnectMenuPage />
        </AkeneoThemeProvider>
    </StrictMode>
));
