import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../test-utils';
import {ConnectedAppCatalogContainer} from '@src/connect/components/ConnectedApp/Catalog/ConnectedAppCatalogContainer';
import {useConnectedApp} from '@src/connect/hooks/use-connected-app';
import {useCatalog} from '@akeneo-pim-community/catalogs';
import {ConnectedAppCatalogPage} from '@src/connect/pages/ConnectedAppCatalogPage';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

jest.mock('@src/connect/components/ConnectedApp/Catalog/ConnectedAppCatalogContainer', () => ({
    ConnectedAppCatalogContainer: jest.fn(() => null),
}));

jest.mock('@src/connect/hooks/use-connected-app');
jest.mock('@akeneo-pim-community/catalogs');

test('The connected app catalog page displays nothing while loading connected app', () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: true, error: null, payload: null}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: false, data: {}}));

    renderWithProviders(<ConnectedAppCatalogPage />);

    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});

test('The connected app catalog page displays nothing while loading catalog', () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: null, payload: {}}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: true, isError: false, data: undefined}));

    renderWithProviders(<ConnectedAppCatalogPage />);

    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});

test('The connected app catalog page renders with a connected app', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [
            {
                icon: 'catalog_structure',
                type: 'view',
                entities: 'catalog_structure',
            },
        ],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        connection_username: 'willy',
    };

    const catalog = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Store FR',
        enabled: true,
        owner_username: 'willy',
    };

    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: null, payload: connectedApp}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: false, data: catalog}));

    renderWithProviders(<ConnectedAppCatalogPage />);
    await waitFor(() => expect(ConnectedAppCatalogContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppCatalogContainer).toHaveBeenCalledWith({connectedApp: connectedApp, catalog: catalog}, {});
});

test('The connected app catalog page renders with not found connected app', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: 'NOT_FOUND', payload: null}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: false, data: {}}));

    renderWithProviders(<ConnectedAppCatalogPage />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found'));

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});

test('The connected app catalog page renders with wrong ACLs', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: 'FORBIDDEN', payload: null}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: false, data: {}}));

    renderWithProviders(<ConnectedAppCatalogPage />);
    await waitFor(() => screen.getByText('error.forbidden'));

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(screen.queryByText('error.forbidden')).toBeInTheDocument();
    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});

test('The connected app catalog page renders with not found catalog', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: null, payload: {}}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: true, data: undefined}));

    renderWithProviders(<ConnectedAppCatalogPage />);
    await waitFor(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found')
    );

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});

test('The connected app catalog page renders with different usernames', async () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: [
            {
                icon: 'catalog_structure',
                type: 'view',
                entities: 'catalog_structure',
            },
        ],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        connection_username: 'admin',
    };

    const catalog = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Store FR',
        enabled: true,
        owner_username: 'willy',
    };

    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: null, payload: connectedApp}));
    (useCatalog as jest.Mock).mockImplementation(() => ({isLoading: false, isError: false, data: catalog}));

    renderWithProviders(<ConnectedAppCatalogPage />);
    await waitFor(() =>
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found')
    );

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppCatalogContainer).not.toHaveBeenCalled();
});
