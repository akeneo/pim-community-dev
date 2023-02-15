import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {renderWithProviders} from '../../../../test-utils';
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
    renderWithProviders(<CustomAppList customApps={customApps} isLimitReached={false} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(screen.queryByText('customApp1')).toBeInTheDocument();
    expect(screen.queryByText('customApp2')).toBeInTheDocument();
});

test('it displays nothing when total is 0', () => {
    const customApps = {
        total: 0,
        apps: [],
    };
    renderWithProviders(<CustomAppList customApps={customApps} isLimitReached={false} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.custom_apps.title')
    ).not.toBeInTheDocument();
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
            <CustomAppList customApps={customApps} isLimitReached={false} />
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
            <CustomAppList customApps={customApps} isLimitReached={true} />
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
