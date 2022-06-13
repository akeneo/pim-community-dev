import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {ConnectedAppCard} from '@src/connect/components/ConnectedApps/ConnectedAppCard';
import {renderWithProviders} from '../../../../test-utils';
import {ConnectedTestAppList} from '@src/connect/components/ConnectedApps/ConnectedTestAppList';
import {useFeatureFlags} from '@src/shared/feature-flags';

beforeEach(() => {
    jest.clearAllMocks();
});

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn(() => {
        return {
            isEnabled: () => true,
        };
    }),
}));

jest.mock('@src/connect/components/ConnectedApps/ConnectedAppCard', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApps/ConnectedAppCard'),
    ConnectedAppCard: jest.fn(() => null),
}));

const connectedTestApps = [
    {
        id: 'test_id_a',
        name: 'App A',
        scopes: [],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'user_group_a',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_test_app: true,
        is_pending: false,
        has_outdated_scopes: false,
    },
    {
        id: 'test_id_b',
        name: 'App B',
        scopes: [],
        connection_code: 'connectionCodeB',
        logo: 'http://www.example.test/path/to/logo/b',
        author: 'author B',
        user_group_name: 'user_group_b',
        connection_username: 'Connection Username',
        categories: [],
        certified: false,
        partner: 'partner B',
        activate_url: 'http://www.example.com/activate',
        is_test_app: true,
        is_pending: false,
        has_outdated_scopes: false,
    },
];

test('it renders list of connected apps', () => {
    renderWithProviders(<ConnectedTestAppList connectedTestApps={connectedTestApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(ConnectedAppCard).toHaveBeenNthCalledWith(1, {item: connectedTestApps[0]}, {});
    expect(ConnectedAppCard).toHaveBeenNthCalledWith(2, {item: connectedTestApps[1]}, {});
});

test('it does not render if list is empty', () => {
    renderWithProviders(<ConnectedTestAppList connectedTestApps={[]} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).not.toBeInTheDocument();

    expect(ConnectedAppCard).not.toHaveBeenCalled();
});

test('it does not render if feature flag is disabled', () => {
    (useFeatureFlags as jest.Mock).mockImplementation(() => ({isEnabled: () => false}));

    renderWithProviders(<ConnectedTestAppList connectedTestApps={connectedTestApps} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.test_apps.title')
    ).not.toBeInTheDocument();

    expect(ConnectedAppCard).not.toHaveBeenCalled();
});
