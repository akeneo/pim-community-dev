import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../test-utils';
import {ConnectedAppPage} from '@src/connect/pages/ConnectedAppPage';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn().mockReturnValue({isEnabled: () => true}),
}));

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useParams: jest.fn().mockReturnValue({connectionCode: 'some_connection_code'}),
}));

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppContainer', () => ({
    ConnectedAppContainer: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app page renders with a connected app', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [
            {
                icon: 'catalog_structure',
                type: 'view',
                entities: 'catalog_structure',
            },
        ],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectionCode=some_connection_code': {
            json: connectedApp,
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await waitFor(() => expect(ConnectedAppContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
});

test('The connected app page renders with internal api errors', async () => {
    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectionCode=some_connection_code': {
            reject: true,
            json: {},
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await waitForElement(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    );

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});
