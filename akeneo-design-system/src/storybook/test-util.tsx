import React, {ComponentType, ReactElement, ReactNode} from 'react';
import {render, RenderOptions} from '@testing-library/react';
import AkeneoPim from '../themes/akeneo-pim';
import { ThemeProvider } from 'styled-components';

const AllTheProviders = ({children}: {children: ReactNode}) => {
    return (
        <ThemeProvider theme={AkeneoPim}>
            {children}
        </ThemeProvider>
    );
};

const customRender = (ui: ReactElement, options?: Omit<RenderOptions, 'queries'>) =>
    render(ui, { wrapper: AllTheProviders as ComponentType, ...options });

export * from '@testing-library/react';
export { customRender as render };
