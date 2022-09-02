import React from 'react';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {onboarderTheme} from 'akeneo-design-system';
import {IntlProvider} from 'react-intl';
import {MemoryRouter} from 'react-router-dom';
import {ToastProvider} from '../utils/toaster';
import {QueryClientProvider} from 'react-query';
import {queryClient} from '../api';

// eslint-disable-next-line
const DefaultProviders = ({children}: any) => (
    <ThemeProvider theme={onboarderTheme}>
        <IntlProvider locale="en" defaultLocale="en" messages={{}}>
            <ToastProvider>
                <QueryClientProvider client={queryClient}>
                    <MemoryRouter>{children}</MemoryRouter>
                </QueryClientProvider>
            </ToastProvider>
        </IntlProvider>
    </ThemeProvider>
);
const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

// eslint-disable-next-line
const renderHookWithProviders = (hook: () => any) =>
    renderHook(hook, {
        // eslint-disable-next-line
        wrapper: ({children}: any) => (
            <ThemeProvider theme={onboarderTheme}>
                <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
            </ThemeProvider>
        ),
    });

export {renderWithProviders, renderHookWithProviders};
