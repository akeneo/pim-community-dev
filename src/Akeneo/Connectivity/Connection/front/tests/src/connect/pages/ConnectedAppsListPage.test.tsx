import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {historyMock, MockFetchResponses, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {ConnectedAppsListPage} from '@src/connect/pages/ConnectedAppsListPage';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {useFeatureFlags} from '@src/shared/feature-flags';
import {ConnectedAppsContainer} from '@src/connect/components/ConnectedApps/ConnectedAppsContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    useFeatureFlags: jest.fn().mockImplementation(() => ({
        isEnabled: () => true,
    })),
}));

jest.mock('@src/connect/components/ConnectedApps/ConnectedAppsContainer', () => ({
    ConnectedAppsContainer: jest.fn().mockImplementation(() => null),
}));

const notify = jest.fn();

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    notify.mockClear();
});

test('The connected apps list page renders with 2 connected apps card', async () => {
    const connectedApps = [
        {
            id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            name: 'App A',
            scopes: ['scope A1'],
            connection_code: 'connectionCodeA',
            logo: 'http://www.example.test/path/to/logo/a',
            author: 'author A',
            user_group_name: 'app_123456abcde',
            categories: ['category A1', 'category A2'],
            certified: false,
            partner: 'partner A',
        },
        {
            id: '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            name: 'App B',
            scopes: ['scope B1', 'scope B2'],
            connection_code: 'connectionCodeB',
            logo: 'http://www.example.test/path/to/logo/b',
            author: 'author B',
            user_group_name: 'app_7891011ghijklm',
            categories: ['category B1'],
            certified: true,
            partner: null,
        },
    ];

    const fetchConnectedAppsResponses: MockFetchResponses = {
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            json: connectedApps,
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppsResponses,
    });

    renderWithProviders(<ConnectedAppsListPage />);

    await waitFor(() => expect(ConnectedAppsContainer).toHaveBeenCalledWith({allConnectedApps: connectedApps}, {}));
});

test('The connected apps list page renders with internal api errors', async () => {
    const fetchConnectedAppsResponses: MockFetchResponses = {
        akeneo_connectivity_connection_apps_rest_get_all_connected_apps: {
            reject: true,
            json: {},
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppsResponses,
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppsListPage />
        </NotifyContext.Provider>
    );
    await waitFor(() => expect(notify).toHaveBeenCalledTimes(1));

    expect(notify).toBeCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.list.flash.error'
    );
});
