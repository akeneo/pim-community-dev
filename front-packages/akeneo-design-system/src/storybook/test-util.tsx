import React, {ComponentType, ReactElement} from 'react';
import {render, RenderOptions} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from '../theme/pim';

const wrapper: ComponentType = ({children}) => <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>;
const customRender = (ui: ReactElement, options?: Omit<RenderOptions, 'queries'>) => render(ui, {wrapper, ...options});

export * from '@testing-library/react';
export {customRender as render};
