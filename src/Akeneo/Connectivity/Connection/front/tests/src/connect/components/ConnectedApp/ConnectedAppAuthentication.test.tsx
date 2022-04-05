import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
import {ConnectedAppAuthentication} from '@src/connect/components/ConnectedApp/ConnectedAppAuthentication';
import {screen, waitFor} from '@testing-library/react';
import {ConsentList} from '@src/connect/components/AppWizard/steps/Authentication/ConsentList';
import fetchMock from 'jest-fetch-mock';
import {useAuthenticationScopes} from '@src/connect/hooks/use-connected-app-authentication-scopes';

jest.mock('@src/connect/hooks/use-connected-app-authentication-scopes', () => ({
    useAuthenticationScopes: jest.fn(() => ({
        isLoading: false,
        authenticationScopes: ['openid', 'email', 'profile'],
    })),
}));

jest.mock('@src/connect/components/AppWizard/steps/Authentication/ConsentList', () => ({
    ConsentList: jest.fn(() => <div>ConsentList</div>),
}));

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['scope 1'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
    is_test_app: false,
    is_pending: false,
};

beforeEach(() => {
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

test('it renders correctly', async () => {
    renderWithProviders(<ConnectedAppAuthentication connectedApp={connectedApp} />);

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

    expect(ConsentList).toHaveBeenCalledWith(
        {
            scopes: ['openid', 'email', 'profile'],
            viewMode: 'settings',
        },
        {}
    );
});

test('it does not render if there is no scopes', async () => {
    (useAuthenticationScopes as jest.Mock).mockImplementation(() => ({
        isLoading: false,
        authenticationScopes: [],
    }));

    renderWithProviders(<ConnectedAppAuthentication connectedApp={connectedApp} />);

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

    expect(ConsentList).not.toHaveBeenCalled();
});
