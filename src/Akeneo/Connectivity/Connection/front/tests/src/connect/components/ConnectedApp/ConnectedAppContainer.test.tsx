import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import userEvent from '@testing-library/user-event';
import useLoadPermissionsFormProviders from '@src/connect/hooks/use-load-permissions-form-providers';

// to make Tab usable with jest
type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
    observe: jest.fn(() => (entryCallback = callback)),
    unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppSettings', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppSettings'),
    ConnectedAppSettings: jest.fn(() => null),
}));

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppPermissions', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppPermissions'),
    ConnectedAppPermissions: jest.fn(() => null),
}));

jest.mock('@src/connect/hooks/use-load-permissions-form-providers', () => ({
    __esModule: true,
    default: jest.fn(() => [null, {}, () => {}]),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app container renders without permissions tab', () => {
    (useLoadPermissionsFormProviders as jest.Mock).mockImplementation(() => [
        [],
        {},
        () => {},
    ]);

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['scope1', 'scope2'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    expect(screen.queryAllByText('App A')).toHaveLength(2);
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).not.toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();
});

test('The connected app container renders with permissions tab', () => {
    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: []
            }
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA']
            }
        }
    };

    (useLoadPermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        () => {},
    ]);

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['scope1', 'scope2'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    expect(screen.queryAllByText('App A')).toHaveLength(2);
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        {
            connectedApp: connectedApp,
        },
        {}
    );
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(ConnectedAppPermissions).toHaveBeenCalledWith(
        expect.objectContaining({
            providers: mockedProviders,
            permissions: mockedPermissions,
        }),
        {}
    );
});

// @todo : test "unsaved change" message is displayed
// @todo : test save is called
