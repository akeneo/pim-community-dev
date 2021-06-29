import React, {StrictMode} from 'react';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {RedirectConnectionSettingsToConnectMenu as RedirectConnectionSettingsToConnectMenuPage} from '../connect/pages/RedirectConnectionSettingsToConnectMenu';

export const RedirectConnectionSettingsToConnectMenu = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <RedirectConnectionSettingsToConnectMenuPage />
        </AkeneoThemeProvider>
    </StrictMode>
));
