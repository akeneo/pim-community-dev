import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import userEvent from '@testing-library/user-event';

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

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app container renders', () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['scope1', 'scope2'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
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
        {
            connectedApp: connectedApp,
        },
        {}
    );
});
