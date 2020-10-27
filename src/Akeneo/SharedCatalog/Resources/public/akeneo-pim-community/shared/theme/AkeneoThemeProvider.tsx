import React, {FC} from 'react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from './theme';

const AkeneoThemeProvider: FC = ({children}) => <ThemeProvider theme={akeneoTheme}>{children}</ThemeProvider>;

export {AkeneoThemeProvider};
