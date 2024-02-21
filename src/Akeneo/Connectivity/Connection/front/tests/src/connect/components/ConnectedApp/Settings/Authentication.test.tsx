import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../../test-utils';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useAuthenticationScopes} from '@src/connect/hooks/use-connected-app-authentication-scopes';
import {AuthenticationScopesList} from '@src/connect/components/ConnectedApp/Settings/AuthenticationScopesList';
import {Authentication} from '@src/connect/components/ConnectedApp/Settings/Authentication';

jest.mock('@src/connect/hooks/use-connected-app-authentication-scopes', () => ({
    useAuthenticationScopes: jest.fn(() => ({
        isLoading: false,
        authenticationScopes: ['openid', 'email', 'profile'],
    })),
}));

jest.mock('@src/connect/components/ConnectedApp/Settings/AuthenticationScopesList', () => ({
    AuthenticationScopesList: jest.fn(() => <div>AuthenticationScopesList</div>),
}));

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['scope 1'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    connection_username: 'Connection Username',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
    is_custom_app: false,
    is_pending: false,
    has_outdated_scopes: false,
};

beforeEach(() => {
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

test('it renders correctly', async () => {
    renderWithProviders(<Authentication connectedApp={connectedApp} />);

    await waitFor(() => expect(useAuthenticationScopes).toHaveBeenCalledTimes(1));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.title')
    ).toBeInTheDocument();

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.information',
            {exact: false}
        )
    ).toBeInTheDocument();

    expect(AuthenticationScopesList).toHaveBeenCalledWith(
        {
            scopes: ['openid', 'email', 'profile'],
        },
        {}
    );
});

test('it does not render if there is no scopes', async () => {
    (useAuthenticationScopes as jest.Mock).mockImplementation(() => ({
        isLoading: false,
        authenticationScopes: [],
    }));

    renderWithProviders(<Authentication connectedApp={connectedApp} />);

    await waitFor(() => expect(useAuthenticationScopes).toHaveBeenCalledTimes(1));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.title')
    ).not.toBeInTheDocument();

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authentication.information',
            {exact: false}
        )
    ).not.toBeInTheDocument();

    expect(AuthenticationScopesList).not.toHaveBeenCalled();
});
