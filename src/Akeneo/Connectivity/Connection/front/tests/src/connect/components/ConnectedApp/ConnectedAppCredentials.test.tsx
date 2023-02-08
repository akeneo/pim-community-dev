import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {ConnectedAppCredentials} from '@src/connect/components/ConnectedApp/Settings/ConnectedAppCredentials';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app credentials renders with secret', async () => {
    mockFetchResponses({
        '/rest/custom-apps/0dfce574-2238-4b13-b8cc-8d257ce7645b/secret': {
            json: '******************************ZmNQ',
        },
    });

    const connectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'Custom App A',
        scopes: ['view_catalog_structure', 'read_products'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        is_custom_app: true,
        is_pending: false,
        has_outdated_scopes: false,
    };

    renderWithProviders(<ConnectedAppCredentials connectedApp={connectedApp} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.title')
    ).toBeInTheDocument();

    expect(screen.queryByText(connectedApp.id)).toBeInTheDocument();

    expect(await screen.findByText('******************************ZmNQ')).toBeInTheDocument();
});
