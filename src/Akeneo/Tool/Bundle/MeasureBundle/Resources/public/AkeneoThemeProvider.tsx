import React, {ReactNode} from 'react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';

const AkeneoThemeProvider = ({children}: { children?: ReactNode }) => (
  <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>
);

export {AkeneoThemeProvider};
