import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../../test-utils';
import {AuthenticationScopesList} from '@src/connect/components/ConnectedApp/Settings/AuthenticationScopesList';

test('it displays both profile & email scopes', () => {
    renderWithProviders(<AuthenticationScopesList scopes={['profile', 'email']} />);

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

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.openid_only'
        )
    ).not.toBeInTheDocument();
});

test('it displays only the profile scope', () => {
    renderWithProviders(<AuthenticationScopesList scopes={['profile']} />);

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

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.openid_only'
        )
    ).not.toBeInTheDocument();
});

test('it displays only the email scope', () => {
    renderWithProviders(<AuthenticationScopesList scopes={['email']} />);

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

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.openid_only'
        )
    ).not.toBeInTheDocument();
});

test('it displays only the openid message', () => {
    renderWithProviders(<AuthenticationScopesList scopes={['openid']} />);

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.openid_only'
        )
    ).toBeInTheDocument();

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_profile', {
            exact: false,
        })
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.apps.wizard.authentication.scope_email', {
            exact: false,
        })
    ).not.toBeInTheDocument();
});
