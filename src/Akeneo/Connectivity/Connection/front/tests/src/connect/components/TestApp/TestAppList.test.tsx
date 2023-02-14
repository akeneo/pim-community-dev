import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {mockFetchResponses, renderWithProviders} from '../../../../test-utils';
import {TestAppList} from '@src/connect/components/TestApp/TestAppList';
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

const testApp1 = {
    id: 'id1',
    name: 'testApp1',
    logo: null,
    author: 'AuthorName',
    url: null,
    activate_url: 'test_app_1_activate_url',
    callback_url: 'test_app_1_callback_url',
    connected: false,
};

const testApp2 = {
    id: 'id2',
    name: 'testApp2',
    logo: null,
    author: null,
    url: null,
    activate_url: 'test_app_2_activate_url',
    callback_url: 'test_app_2_callback_url',
    connected: true,
};

test('it displays test app', () => {
    const testApps = {
        total: 2,
        apps: [testApp1, testApp2],
    };
    renderWithProviders(<TestAppList testApps={testApps} isLimitReached={false} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.total', {exact: false})
    ).toBeInTheDocument();

    expect(screen.queryByText('testApp1')).toBeInTheDocument();
    expect(screen.queryByText('testApp2')).toBeInTheDocument();
});

test('it displays nothing when total is 0', () => {
    const testApps = {
        total: 0,
        apps: [],
    };
    renderWithProviders(<TestAppList testApps={testApps} isLimitReached={false} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.marketplace.test_apps.title')
    ).not.toBeInTheDocument();
});

test('it disabled the connect button when the user doesnt have the permission to open Apps', () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return false;
        }
        return true;
    });

    const testApps = {
        total: 1,
        apps: [testApp1],
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <TestAppList testApps={testApps} isLimitReached={false} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('testApp1')).toBeInTheDocument();

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

    const testApps = {
        total: 1,
        apps: [testApp1],
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <TestAppList testApps={testApps} isLimitReached={true} />
        </SecurityContext.Provider>
    );

    expect(screen.queryByText('testApp1')).toBeInTheDocument();

    const connectButton = expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.connect'));

    connectButton.toHaveAttribute('disabled');
    connectButton.toHaveAttribute('aria-disabled', 'true');

    expect(
        screen.queryByText('akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached')
    ).toBeInTheDocument();
});
