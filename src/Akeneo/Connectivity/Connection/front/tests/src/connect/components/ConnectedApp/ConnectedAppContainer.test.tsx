import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ScopeList} from '@src/connect/components/ScopeList';
import {TabBar} from 'akeneo-design-system';

jest.mock('@src/connect/components/ScopeList', () => ({
    ...jest.requireActual('@src/connect/components/ScopeList'),
    ScopeList: jest.fn(() => null),
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

test('The connected app container renders with scopes', async () => {
    const scopes = [
        {
            icon: 'catalog_structure',
            type: 'view',
            entities: 'catalog_structure'
        },
    ];

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: scopes,
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
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information', {exact: false})
    ).toBeInTheDocument();
    expect(ScopeList).toHaveBeenCalledWith(
        {
            scopeMessages: scopes,
            itemFontSize: 'default'
        },
        {}
    );
});

test('The connected app container renders without scopes', () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [],
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
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information', {exact: false})
    ).toBeInTheDocument();
    expect(ScopeList).not.toHaveBeenCalled();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')).toBeInTheDocument();
});
