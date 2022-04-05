import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../../test-utils';
import {AuthenticationScopes} from '@src/connect/components/ConnectedApp/Settings/AuthenticationScopes';

test('it displays both profile & email scopes', () => {
    renderWithProviders(<AuthenticationScopes scopes={['profile', 'email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).toBeInTheDocument();
});

test('it displays only the profile scope', () => {
    renderWithProviders(<AuthenticationScopes scopes={['profile']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).not.toBeInTheDocument();
});

test('it displays only the email scope', () => {
    renderWithProviders(<AuthenticationScopes scopes={['email']} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).toBeInTheDocument();
});
