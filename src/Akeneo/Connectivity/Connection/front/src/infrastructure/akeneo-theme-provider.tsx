import React, {PropsWithChildren} from 'react';
import {ThemeProvider} from 'styled-components';
import {theme} from '../common/theme';

export const AkeneoThemeProvider = ({children}: PropsWithChildren<{}>) => (
    <ThemeProvider theme={theme}>{children}</ThemeProvider>
);
