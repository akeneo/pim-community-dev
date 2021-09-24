import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {TabBar} from 'akeneo-design-system';

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppSettings', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppSettings'),
    ConnectedAppSettings: jest.fn(() => null),
}));

jest.mock('akeneo-design-system', () => ({
    ...jest.requireActual('akeneo-design-system'),
    TabBar: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app container renders', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['scope1', 'scope2'],
        connection_code: 'some_connection_code',
        logo: 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    expect(screen.queryAllByText('App A')).toHaveLength(2);
    expect(TabBar).toHaveBeenCalledTimes(1);
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        {
            connectedApp: connectedApp
        },
        {}
    );
});
