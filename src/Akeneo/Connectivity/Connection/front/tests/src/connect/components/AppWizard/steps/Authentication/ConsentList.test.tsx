import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {ThemeProvider} from 'styled-components';

test('it displays both profile & email scopes', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authentication appName='MyApp' scopes={['profile', 'email']} />
        </ThemeProvider>
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).toBeInTheDocument();
});

test('it displays only the profile scope', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authentication appName='MyApp' scopes={['profile']} />
        </ThemeProvider>
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).not.toBeInTheDocument();
});

test('it displays only the email scope', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <Authentication appName='MyApp' scopes={['email']} />
        </ThemeProvider>
    );

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).toBeInTheDocument();
});
