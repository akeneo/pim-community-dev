import React from 'react';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom/extend-expect';
import {render} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {onboarderTheme} from 'akeneo-design-system';

// eslint-disable-next-line
const DefaultProviders = ({children}: any) => <ThemeProvider theme={onboarderTheme}>{children}</ThemeProvider>;

const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

// eslint-disable-next-line
const renderHookWithProviders = (hook: () => any) => renderHook(hook, {wrapper: DefaultProviders});

export {renderWithProviders, renderHookWithProviders};
