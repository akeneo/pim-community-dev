import {Authentication} from '@src/connect/components/AppWizard/steps/Authentication/Authentication';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import React from 'react';
import {renderWithProviders} from '../../../../../../test-utils';

test('it displays both profile & email scopes', () => {
    renderWithProviders(<Authentication appName='MyApp' scopes={['profile', 'email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).toBeInTheDocument();
});

test('it displays only the profile scope', () => {
    renderWithProviders(<Authentication appName='MyApp' scopes={['profile']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).not.toBeInTheDocument();
});

test('it displays only the email scope', () => {
    renderWithProviders(<Authentication appName='MyApp' scopes={['email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email')
    ).toBeInTheDocument();
});
