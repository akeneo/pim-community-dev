import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, wait, waitForElement} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, MockFetchResponses, mockFetchResponses} from '../../../test-utils';
import {ConnectedAppPage} from '@src/connect/pages/ConnectedAppPage';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';

jest.mock('@src/shared/feature-flags/use-feature-flags', () => ({
    ...jest.requireActual('@src/shared/feature-flags/use-feature-flags'),
    useFeatureFlags: jest.fn().mockReturnValue({isEnabled: () => true}),
}));

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useParams: jest.fn().mockReturnValue({connectionCode: 'some_connection_code'}),
}));

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppContainer', () => ({
    ConnectedAppContainer: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app page renders with some scopes', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [
            {
                icon: 'catalog_structure',
                type: 'view',
                entities: 'catalog_structure'
            },
        ],
        connection_code: 'some_connection_code',
        logo: 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectionCode=some_connection_code': {
            json: connectedApp,
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await wait(() => expect(ConnectedAppContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).toHaveBeenCalledWith({connectedApp: connectedApp}, {});


    // await waitForElement(() => screen.getAllByText('App A'));
    //
    // expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    // expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    // expect(screen.queryAllByText('App A')).toHaveLength(2);
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')).toBeInTheDocument();
    // expect(
    //     screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information', {exact: false})
    // ).toBeInTheDocument();
    // expect(screen.queryAllByText('akeneo_connectivity.connection.connect.apps.scope.type.view', {exact: false})).toHaveLength(2);
    // expect(screen.queryAllByText('akeneo_connectivity.connection.connect.apps.scope.type.edit', {exact: false})).toHaveLength(3);
    // expect(screen.queryAllByText('akeneo_connectivity.connection.connect.apps.scope.type.delete', {exact: false})).toHaveLength(2);
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.catalog_structure')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.attribute_options')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.categories')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.channel_localization')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.channel_settings')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.association_types')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.products')).toBeInTheDocument();
});

test('The connected app page renders without scopes', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [],
        connection_code: 'connectionCode',
        logo: 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
    };

    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectionCode=some_connection_code': {
            json: connectedApp,
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await wait(() => expect(ConnectedAppContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).toHaveBeenCalledWith({connectedApp: connectedApp}, {});


    // await waitForElement(() => screen.getAllByText('App A'));
    //
    // expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    // expect(screen.queryAllByText('pim_menu.item.connected_apps')).toHaveLength(2);
    // expect(screen.queryAllByText('App A')).toHaveLength(2);
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')).toBeInTheDocument();
    // expect(
    //     screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.information', {exact: false})
    // ).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.settings.authorizations.no_scope')).toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.type.view', {exact: false})).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.type.edit', {exact: false})).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.type.delete', {exact: false})).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.catalog_structure')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.attribute_options')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.categories')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.channel_localization')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.channel_settings')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.association_types')).not.toBeInTheDocument();
    // expect(screen.queryByText('akeneo_connectivity.connection.connect.apps.scope.entities.products')).not.toBeInTheDocument();
});

test('The connected app page renders with internal api errors', async () => {
    const fetchConnectedAppResponses: MockFetchResponses = {
        'akeneo_connectivity_connection_apps_rest_get_connected_app?connectionCode=some_connection_code': {
            reject: true,
            json: {},
        },
    };

    mockFetchResponses({
        ...fetchConnectedAppResponses,
    });

    renderWithProviders(<ConnectedAppPage />);
    await waitForElement(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found'));

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')).toBeInTheDocument();
    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});
