import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import userEvent from '@testing-library/user-event';
import {act} from '@testing-library/react-hooks';
import {setLogger} from 'react-query';
import {DeleteCustomAppPromptPage} from '@src/connect/pages/DeleteCustomAppPromptPage';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';

setLogger({
    log: () => null,
    warn: () => null,
    error: () => null, // explicit error generation triggers react query to log the error
});

jest.mock('react-router', () => ({
    ...jest.requireActual('react-router'),
    useParams: jest.fn().mockReturnValue({customAppId: 'appId'}),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('it displays the delete custom app prompt page and successfully deletes a custom app', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId': {
            json: {},
            status: 200,
        },
    });

    const notify = jest.fn();

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <DeleteCustomAppPromptPage />
        </NotifyContext.Provider>
    );

    assertItDisplaysPromptPage();

    act(() => {
        userEvent.click(screen.getByText('pim_common.delete'));
    });

    await waitFor(() =>
        expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace')
    );

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId',
        expect.objectContaining({
            method: 'DELETE',
        })
    );

    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.custom_apps.delete_modal.flash.success'
    );

    done();
});

test('it gracefully fails to delete a custom app', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId': {
            json: {},
            status: 500,
        },
    });

    const notify = jest.fn();

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <DeleteCustomAppPromptPage />
        </NotifyContext.Provider>
    );

    assertItDisplaysPromptPage();

    act(() => {
        userEvent.click(screen.getByText('pim_common.delete'));
    });

    await waitFor(() =>
        expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace')
    );

    expect(fetchMock).toHaveBeenCalledWith(
        'akeneo_connectivity_connection_custom_apps_rest_delete?customAppId=appId',
        expect.objectContaining({
            method: 'DELETE',
        })
    );

    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.custom_apps.delete_modal.flash.error'
    );

    done();
});

test('it redirects to the App Store when prompt page is closed', () => {
    renderWithProviders(<DeleteCustomAppPromptPage />);

    act(() => {
        userEvent.click(screen.getByTitle('pim_common.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace');
});

test('it redirects to the App Store when user cancels deletion', () => {
    renderWithProviders(<DeleteCustomAppPromptPage />);

    act(() => {
        userEvent.click(screen.getByText('pim_common.cancel'));
    });

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace');
});

const assertItDisplaysPromptPage = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.delete_modal.subtitle')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.delete_modal.title')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.delete_modal.description')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.delete_modal.warning')
    ).toBeInTheDocument();
    expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();
    expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
};
