import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../../test-utils';
import {ScopeList} from '@src/connect/components/ScopeList';
import {ConnectedAppAuthorizations} from '@src/connect/components/ConnectedApp/ConnectedAppAuthorizations';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

jest.mock('@src/connect/components/ScopeList', () => ({
    ...jest.requireActual('@src/connect/components/ScopeList'),
    ScopeList: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app authorizations renders with scopes', async () => {
    const scopes = [
        {
            icon: 'catalog_structure',
            type: 'view',
            entities: 'catalog_structure',
        },
        {
            icon: 'products',
            type: 'view',
            entities: 'products',
        },
    ];

    const fetchConnectedAppScopeMessagesResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_all_connected_app_scope_messages?connectionCode=some_connection_code':
            {
                json: scopes,
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppScopeMessagesResponses,
    });

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['view_catalog_structure', 'read_products'],
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

    renderWithProviders(<ConnectedAppAuthorizations connectedApp={connectedApp} />);
    await waitFor(() => expect(ScopeList).toHaveBeenCalledTimes(1));

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
            {exact: false}
        )
    ).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_access_to_product_information'
        )
    ).not.toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: scopes,
            itemFontSize: 'default',
        },
        {}
    );
});

test('The connected app authorizations renders with an additional helper if there is no product scope', async () => {
    const scopes = [
        {
            icon: 'catalog_structure',
            type: 'view',
            entities: 'catalog_structure',
        },
    ];

    const fetchConnectedAppScopeMessagesResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_all_connected_app_scope_messages?connectionCode=some_connection_code':
            {
                json: scopes,
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppScopeMessagesResponses,
    });

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['view_catalog_structure'],
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

    renderWithProviders(<ConnectedAppAuthorizations connectedApp={connectedApp} />);
    await waitFor(() => expect(ScopeList).toHaveBeenCalledTimes(1));

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
            {exact: false}
        )
    ).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_access_to_product_information'
        )
    ).toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: scopes,
            itemFontSize: 'default',
        },
        {}
    );
});

test('The connected app authorizations renders without scopes', async () => {
    const fetchConnectedAppScopeMessagesResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_all_connected_app_scope_messages?connectionCode=some_connection_code':
            {
                json: [],
            },
    };

    mockFetchResponses({
        ...fetchConnectedAppScopeMessagesResponses,
    });

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [],
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

    renderWithProviders(<ConnectedAppAuthorizations connectedApp={connectedApp} />);
    await waitFor(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')
    );

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information',
            {exact: false}
        )
    ).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_access_to_product_information'
        )
    ).toBeInTheDocument();
    expect(ScopeList).not.toHaveBeenCalled();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope'
        )
    ).toBeInTheDocument();
});
