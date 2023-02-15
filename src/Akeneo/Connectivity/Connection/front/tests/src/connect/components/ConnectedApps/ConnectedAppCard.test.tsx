import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppCard} from '@src/connect/components/ConnectedApps/ConnectedAppCard';
import {SecurityContext} from '@src/shared/security';
import {AppIllustration} from 'akeneo-design-system';
import {ConnectedApp} from '@src/model/Apps/connected-app';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
});

jest.mock('akeneo-design-system', () => ({
    ...jest.requireActual('akeneo-design-system'),
    AppIllustration: jest.fn(() => null),
}));

test('The connected app card renders', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=author+A'
        )
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

test('The Manage App button is disabled when the user doesnt have the permission to Manage Apps', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return false;
        }
        return true;
    });

    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <ConnectedAppCard item={item} />
        </SecurityContext.Provider>
    );
    await waitFor(() => screen.getByText('App A'));

    const manageAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    );
    manageAppButton.not.toHaveAttribute('href');
    manageAppButton.toHaveAttribute('disabled');
    manageAppButton.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is disabled when the user doesnt have the permission to Open Apps', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_open_apps') {
            return false;
        }
        return true;
    });

    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <ConnectedAppCard item={item} />
        </SecurityContext.Provider>
    );
    await waitFor(() => screen.getByText('App A'));

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')
    );
    openAppButton.not.toHaveAttribute('href');
    openAppButton.toHaveAttribute('disabled');
    openAppButton.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is enabled for custom app when the user has the permission to open apps', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_open_apps') {
            return true;
        }
        return false;
    });

    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: true,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <ConnectedAppCard item={item} />
        </SecurityContext.Provider>
    );
    await waitFor(() => screen.getByText('App A'));

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')
    );
    openAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_open?connectionCode=connectionCodeA'
    );
    openAppButton.not.toHaveAttribute('disabled');
    openAppButton.not.toHaveAttribute('aria-disabled', 'true');
});

test('The Manage App button is enabled for custom app when the user has the permission to manage apps', async () => {
    const isGranted = jest.fn(acl => {
        if (acl === 'akeneo_connectivity_connection_manage_apps') {
            return true;
        }
        return false;
    });

    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: true,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <ConnectedAppCard item={item} />
        </SecurityContext.Provider>
    );
    await waitFor(() => screen.getByText('App A'));

    const manageAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    );
    manageAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_edit?connectionCode=connectionCodeA'
    );
    manageAppButton.not.toHaveAttribute('disabled');
    manageAppButton.not.toHaveAttribute('aria-disabled', 'true');
});

test('The connected app card displays removed user as author when author is null', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: null,
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=akeneo_connectivity.connection.connect.connected_apps.list.custom_apps.removed_user'
        )
    ).toBeInTheDocument();
});

test('The connected app card displays app illustration when logo is null', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: null,
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(screen.queryByAltText('App A')).not.toBeInTheDocument();
    expect(AppIllustration).toHaveBeenCalled();
});

test('The connected app card displays a warning when it is not listed on the app store', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: false,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.not_listed_on_the_appstore')
    ).toBeInTheDocument();
});

test('The pending App card renders', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: true,
        has_outdated_scopes: false,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=author+A'
        )
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.pending')
    ).toBeInTheDocument();
    expect(screen.queryByText('category A1')).not.toBeInTheDocument();
    expect(screen.queryByText('category A2')).toBeNull();

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')
    );
    openAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_open?connectionCode=connectionCodeA'
    );
    openAppButton.not.toHaveAttribute('disabled');
    openAppButton.not.toHaveAttribute('aria-disabled', 'true');

    const manageAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.manage_app')
    );
    manageAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_edit?connectionCode=connectionCodeA'
    );
    manageAppButton.not.toHaveAttribute('disabled');
    manageAppButton.not.toHaveAttribute('aria-disabled', 'true');

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(screen.queryByAltText('App A')).toBeInTheDocument();
    expect(AppIllustration).toHaveBeenCalled();
});

test('The connected app card displays a warning when it has a outdated scopes flag', async () => {
    const item: ConnectedApp = {
        id: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        name: 'App A',
        scopes: ['scope A1'],
        connection_code: 'connectionCodeA',
        logo: 'http://www.example.test/path/to/logo/a',
        author: 'author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'Connection Username',
        categories: ['category A1', 'category A2'],
        certified: false,
        partner: 'partner A',
        activate_url: 'http://www.example.com/activate',
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: true,
        is_loaded: true,
        is_listed_on_the_appstore: true,
    };

    renderWithProviders(<ConnectedAppCard item={item} />);
    await waitFor(() => screen.getByText('App A'));

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.new_access_authorization_required'
        )
    ).toBeInTheDocument();

    expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.list.card.open_app')).toHaveStyle(
        'background-color: rgb(249, 181, 63)'
    );
});
