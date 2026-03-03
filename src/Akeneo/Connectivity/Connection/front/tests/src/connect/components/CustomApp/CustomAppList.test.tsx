import React from 'react';
import {screen, waitFor} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {mockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {CustomAppList} from '@src/connect/components/CustomApps/CustomAppList';
import {SecurityContext} from '@src/shared/security';

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

const customApp1 = {
    id: 'id1',
    name: 'customApp1',
    logo: null,
    author: 'AuthorName',
    url: null,
    activate_url: 'custom_app_1_activate_url',
    callback_url: 'custom_app_1_callback_url',
    connected: false,
};

const customApp2 = {
    id: 'id2',
    name: 'customApp2',
    logo: null,
    author: null,
    url: null,
    activate_url: 'custom_app_2_activate_url',
    callback_url: 'custom_app_2_callback_url',
    connected: true,
};

test('it displays custom app', () => {
    const customApps = {
        total: 2,
        apps: [customApp1, customApp2],
    };
    renderWithProviders(<CustomAppList customApps={customApps} isConnectLimitReached={false} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(screen.queryByText('customApp1')).toBeInTheDocument();
    expect(screen.queryByText('customApp2')).toBeInTheDocument();
});

test('it disabled the connect button when the user doesnt have the permission to open Apps', () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return false;
        }
        return true;
    });

    const customApps = {
        total: 1,
        apps: [customApp1],
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <CustomAppList customApps={customApps} isConnectLimitReached={false} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('customApp1')).toBeInTheDocument();

    const connectButton = expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect'));

    connectButton.toHaveAttribute('disabled');
    connectButton.toHaveAttribute('aria-disabled', 'true');
});

test('it disabled the connect button and show a warning when the limit of connected app is reached', () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return true;
        }
        return true;
    });

    const customApps = {
        total: 1,
        apps: [customApp1],
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <CustomAppList customApps={customApps} isConnectLimitReached={true} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('customApp1')).toBeInTheDocument();

    const connectButton = expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect'));

    connectButton.toHaveAttribute('disabled');
    connectButton.toHaveAttribute('aria-disabled', 'true');

    expect(
        screen.queryByText('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached')
    ).toBeInTheDocument();
});

test('it show both warning when the limit of custom app created is reached and the limit of connected app is reached', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return true;
        }
        return true;
    });

    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_max_limit_reached: {
            json: true,
            status: 200,
        },
    });

    const customApps = {
        total: 1,
        apps: [customApp1],
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <CustomAppList customApps={customApps} isConnectLimitReached={true} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('customApp1')).toBeInTheDocument();

    await waitFor(() =>
        screen.findByText('akeneo_connectivity.connection.connect.custom_apps.creation_limit_reached', {exact: false})
    );

    await screen.findByText('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached', {
        exact: false,
    });
});
