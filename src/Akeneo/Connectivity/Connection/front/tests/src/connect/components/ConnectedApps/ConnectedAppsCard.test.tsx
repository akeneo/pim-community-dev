import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppCard} from '@src/connect/components/ConnectedApps/ConnectedAppCard';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

test('The connected app card renders', async () => {
    const item = {
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
        activate_url: 'http://www.example.com/activate',
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by' + ' author A')
    ).toBeInTheDocument();
    expect(screen.queryByText('category A1')).toBeInTheDocument();
    expect(screen.queryByText('category A2')).toBeNull();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')
    ).toBeInTheDocument();
});
