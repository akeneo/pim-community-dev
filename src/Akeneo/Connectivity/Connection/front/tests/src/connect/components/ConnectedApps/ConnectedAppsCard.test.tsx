import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppCard} from '@src/connect/components/ConnectedApps/ConnectedAppCard';
import {SecurityContext} from '@src/shared/security';

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
        author: 'authorA',
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
        screen.queryByText(
            'akeneo_connectivity.connection.connect.connected_apps.list.card.developed_by?author=authorA'
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
