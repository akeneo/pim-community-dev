import React, {StrictMode} from 'react';
import {AkeneoThemeProvider} from './akeneo-theme-provider';
import {withDependencies} from './dependencies-provider';
import {RedirectToConnectMenu as RedirectToConnectMenuPage} from '../connect/pages/RedirectToConnectMenu';

type Props = {
    url: string;
};

export const RedirectToConnectMenu = withDependencies<Props>(({url}: Props) => (
    <StrictMode>
        <AkeneoThemeProvider>
            <RedirectToConnectMenuPage url={url} />
        </AkeneoThemeProvider>
    </StrictMode>
));
