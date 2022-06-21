import React from 'react';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {onboarderTheme} from 'akeneo-design-system';
import {IntlProvider} from 'react-intl';
import {MemoryRouter} from 'react-router-dom';

// eslint-disable-next-line
const DefaultProviders = ({children}: any) => (
    <ThemeProvider theme={onboarderTheme}>
        <IntlProvider locale="en" defaultLocale="en" messages={{}}>
            <MemoryRouter>{children}</MemoryRouter>
        </IntlProvider>
    </ThemeProvider>
);
const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

export {renderWithProviders};
