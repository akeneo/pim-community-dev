import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../test-utils';
import {ConnectedAppDeletePage} from '@src/connect/pages/ConnectedAppDeletePage';
import {useDeleteApp} from '@src/connect/hooks/use-delete-app';
import userEvent from '@testing-library/user-event';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';

const notify = jest.fn();

jest.mock('@src/connect/hooks/use-delete-app', () => ({
    ...jest.requireActual('@src/connect/hooks/use-delete-app'),
    useDeleteApp: jest.fn().mockImplementation(() => () => Promise.resolve()),
}));

jest.mock('react-router-dom', () => ({
    ...jest.requireActual('react-router-dom'),
    useParams: jest.fn().mockReturnValue({connectionCode: 'some_connection_code'}),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The delete app page renders and I can cancel', () => {
    renderWithProviders(<ConnectedAppDeletePage />);

    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.subtitle')
    ).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.cancel'));

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_connected_apps_edit');
});

test('The delete app page renders and I can delete the app', async done => {
    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppDeletePage />
        </NotifyContext.Provider>
    );

    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.subtitle')
    ).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => expect(useDeleteApp).toHaveBeenCalled());
    await waitFor(() =>
        expect(notify).toHaveBeenCalledWith(
            NotificationLevel.SUCCESS,
            'akeneo_connectivity.connection.connect.connected_apps.delete.flash.success'
        )
    );
    await waitFor(() =>
        expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_connected_apps')
    );
    done();
});

test('The delete app page renders and a notification is shown when the deletion fails', async done => {
    (useDeleteApp as jest.Mock).mockImplementation(() => () => Promise.reject());

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppDeletePage />
        </NotifyContext.Provider>
    );

    expect(screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.title')).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.connected_apps.delete.subtitle')
    ).toBeInTheDocument();

    userEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => expect(useDeleteApp).toHaveBeenCalled());
    await waitFor(() =>
        expect(notify).toHaveBeenCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.connect.connected_apps.delete.flash.error'
        )
    );
    done();
});
