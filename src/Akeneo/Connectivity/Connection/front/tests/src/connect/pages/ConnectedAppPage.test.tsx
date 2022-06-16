import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../test-utils';
import {ConnectedAppPage} from '@src/connect/pages/ConnectedAppPage';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {useConnectedApp} from '@src/connect/hooks/use-connected-app';
import {ConnectedAppContainerIsLoading} from '@src/connect/components/ConnectedApp/ConnectedAppContainerIsLoading';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppContainer', () => ({
    ConnectedAppContainer: jest.fn(() => null),
}));

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppContainerIsLoading', () => ({
    ConnectedAppContainerIsLoading: jest.fn(() => null),
}));

jest.mock('@src/connect/hooks/use-connected-app');

test('The connected app page renders with a loading screen', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: true, error: null, payload: null}));

    renderWithProviders(<ConnectedAppPage />);
    await waitFor(() => expect(ConnectedAppContainerIsLoading).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});

test('The connected app page renders with a connected app', async () => {
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
    };

    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: null, payload: connectedApp}));

    renderWithProviders(<ConnectedAppPage />);
    await waitFor(() => expect(ConnectedAppContainer).toHaveBeenCalledTimes(1));

    expect(ConnectedAppContainer).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
});

test('The connected app page renders with connected app not found', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: 'NOT_FOUND', payload: null}));

    renderWithProviders(<ConnectedAppPage />);
    await waitFor(() => screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found'));

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.not_found')
    ).toBeInTheDocument();
    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});

test('The connected app page renders with wrong ACLs', async () => {
    (useConnectedApp as jest.Mock).mockImplementation(() => ({loading: false, error: 'FORBIDDEN', payload: null}));

    renderWithProviders(<ConnectedAppPage />);
    await waitFor(() => screen.getByText('error.forbidden'));

    expect(screen.queryByText('error.exception', {exact: false})).toBeInTheDocument();
    expect(screen.queryByText('error.forbidden')).toBeInTheDocument();
    expect(ConnectedAppContainer).not.toHaveBeenCalled();
});
