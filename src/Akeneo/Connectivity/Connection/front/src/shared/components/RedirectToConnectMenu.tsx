import {ClientErrorIllustration} from 'akeneo-design-system';
import React, {StrictMode} from 'react';
import {AkeneoThemeProvider} from '../../infrastructure/akeneo-theme-provider';
import {withDependencies} from '../../infrastructure/dependencies-provider';

export const RedirectToConnectMenu = withDependencies(() => (
    <StrictMode>
        <AkeneoThemeProvider>
            <div>
                <ClientErrorIllustration width="auto" height="auto" />
                This page have moved in the Connect space
            </div>
        </AkeneoThemeProvider>
    </StrictMode>
));
