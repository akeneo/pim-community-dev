import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../../test-utils';
import {ConnectedAppsContainer} from '@src/connect/components/ConnectedApps/ConnectedAppsContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    useFeatureFlags: () => {
        return {
            isEnabled: () => {
                return true;
            },
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
            logo: 'http://www.example.com/path/to/logo/a',
            author: 'author A',
            categories: ['category A1', 'category A2'],
            certified: false,
            partner: 'partner A'
        },
        {
            id: '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            name: 'App B',
            scopes: ['scope B1', 'scope B2'],
            connection_code: 'connectionCodeB',
            logo: 'http://www.example.com/path/to/logo/b',
            author: 'author B',
            categories: ['category B1'],
            certified: true,
            partner: null
        }
    ];

    const fetchMarketplaceUrlResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_marketplace_rest_get_web_marketplace_url': {
            json: 'https://fake.marketplace.akeneo.com',
        },
    };

    mockFetchResponses({
        ...fetchMarketplaceUrlResponses,
    });

    renderWithProviders(<ConnectedAppsContainer connectedApps={connectedApps} />);
    await waitForElement(() => screen.getByText('App A'));

    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.title', {exact: false})).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.description_1')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.description_2')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.link')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.title')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.total', {exact: false})).toBeInTheDocument();
    expect(screen.getAllByText('akeneo_connectivity.connection.connect.connected_apps.card.manage_app')).toHaveLength(2);
    expect(screen.getByText('App A')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.card.developed_by' + ' author A')).toBeInTheDocument();
    expect(screen.getByText('category A1')).toBeInTheDocument();
    expect(screen.queryByText('category A2')).toBeNull();
    expect(screen.getByText('App B')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.card.developed_by' + ' author B')).toBeInTheDocument();
    expect(screen.getByText('category B1')).toBeInTheDocument();
});

test('The connected apps list renders without connected apps', async () => {
    const fetchMarketplaceUrlResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_marketplace_rest_get_web_marketplace_url': {
            json: 'https://fake.marketplace.akeneo.com',
        },
    };

    mockFetchResponses({
        ...fetchMarketplaceUrlResponses,
    });

    renderWithProviders(<ConnectedAppsContainer connectedApps={[]} />);
    await waitForElement(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.empty'));

    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.title', {exact: false})).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.description_1')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.description_2')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.helper.link')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.title')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.total', {exact: false})).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.empty')).toBeInTheDocument();
    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.apps.check_marketplace', {exact: false})).toBeInTheDocument();
    expect(screen.queryAllByText('akeneo_connectivity.connection.connect.connected_apps.card.manage_app')).toHaveLength(0);
});
