import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../../test-utils';
import {SecurityContext} from '@src/shared/security';
import {OpenAppButton} from '@src/connect/components/ConnectedApp/OpenAppButton';
import fetchMock from 'jest-fetch-mock';

beforeEach(() => {
    window.sessionStorage.clear();
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['read_products', 'write_products'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    connection_username: 'connection_username',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
    is_custom_app: false,
    is_pending: false,
    has_outdated_scopes: true,
};

test('The Open App button is disabled when the user doesnt have the permission to Open Apps', () => {
    const isGranted = jest.fn((acl: string) => {
        return acl !== 'akeneo_connectivity_connection_open_apps';
    });

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={connectedApp} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.not.toHaveAttribute('href');
    openAppButton.toHaveAttribute('disabled');
    openAppButton.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is enabled when the user has the permission to manage apps', () => {
    const isGranted = jest.fn((acl: string) => {
        return acl === 'akeneo_connectivity_connection_open_apps';
    });

    renderWithProviders(
        <SecurityContext.Provider value={{isGranted}}>
            <OpenAppButton connectedApp={connectedApp} />
        </SecurityContext.Provider>
    );

    const openAppButton = expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    );
    openAppButton.toHaveAttribute(
        'href',
        '#akeneo_connectivity_connection_connect_connected_apps_open?connectionCode=some_connection_code'
    );
    openAppButton.not.toHaveAttribute('disabled');
    openAppButton.not.toHaveAttribute('aria-disabled', 'true');
});

test('The Open App button is in warning state for custom app when the connectedApp has a outdated scopes flag', () => {
    renderWithProviders(<OpenAppButton connectedApp={{...connectedApp, has_outdated_scopes: true}} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    ).toHaveStyle('background-color: rgb(249, 181, 63)');
});

test('The Open App button is in warning state for custom app when the connectedApp is pending', () => {
    renderWithProviders(<OpenAppButton connectedApp={{...connectedApp, is_pending: true}} />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.header.open_app_button.label')
    ).toHaveStyle('background-color: rgb(249, 181, 63)');
});
