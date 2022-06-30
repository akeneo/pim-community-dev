import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../../test-utils';
import {ConnectedAppsContainer} from '@src/connect/components/ConnectedApps/ConnectedAppsContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    useFeatureFlags: () => {
        return {
            isEnabled: () => true,
        };
    },
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The connected apps list renders with 2 connected apps card', async () => {
    const connectedApps = [
        {
            id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            name: 'App A',
            scopes: ['scope A1'],
            connection_code: 'connectionCodeA',
            logo: 'http://www.example.test/path/to/logo/a',
            author: 'authorA',
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
            author: 'authorB',
            user_group_name: 'app_7891011ghijklm',
            categories: ['category B1'],
            certified: true,
            partner: null,
        },
    ];

    renderWithProviders(<ConnectedAppsContainer connectedApps={connectedApps} />);
    await waitFor(() => screen.getByText('App A'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_1')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_2')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.link')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.total', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryAllByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    ).toHaveLength(2);
    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=authorA'
        )
    ).toBeInTheDocument();
    expect(screen.queryByText('category A1')).toBeInTheDocument();
    expect(screen.queryByText('category A2')).toBeNull();
    expect(screen.queryByText('App B')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=authorB'
        )
    ).toBeInTheDocument();
    expect(screen.queryByText('category B1')).toBeInTheDocument();
});

test('The connected apps list renders without connected apps', async () => {
    renderWithProviders(<ConnectedAppsContainer connectedApps={[]} />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.empty'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.title', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_1')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_2')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.helper.link')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.total', {exact: false})
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.empty')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.check_marketplace', {
            exact: false,
        })
    ).toBeInTheDocument();
    expect(
        screen.queryAllByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    ).toHaveLength(0);
});
