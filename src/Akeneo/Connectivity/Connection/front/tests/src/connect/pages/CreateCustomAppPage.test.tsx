import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {historyMock, mockFetchResponses, renderWithProviders} from '../../../test-utils';
import {CreateCustomAppPage} from '@src/connect/pages/CreateCustomAppPage';
import userEvent from '@testing-library/user-event';
import {act} from '@testing-library/react-hooks';
import {setLogger} from 'react-query';

setLogger({
    log: () => null,
    warn: () => null,
    error: () => null, // explicit error generation triggers react query to log the error
});

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('it renders the form without credentials and display them when form is submitted', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_create: {
            json: {
                clientId: '123f1076-3a55-4e4a-bc38-1288c88c79c1',
                clientSecret: '306dd4047d4d4e4791c571288c88c79c1ad4b7837842',
            },
            status: 200,
        },
    });

    renderWithProviders(<CreateCustomAppPage />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.subtitle')
    ).toBeInTheDocument();

    assertItDisplaysTheForm();
    submitTheFormWith('My custom app', 'https://example.com/activate-url', 'https://example.com/callback-url');

    await waitFor(() =>
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.title')
    );

    assertItDisplaysCustomAppCredentials(
        '123f1076-3a55-4e4a-bc38-1288c88c79c1',
        '306dd4047d4d4e4791c571288c88c79c1ad4b7837842'
    );

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"My custom app","activateUrl":"https://example.com/activate-url","callbackUrl":"https://example.com/callback-url"}',
        headers: {
            'Content-type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
    });

    userEvent.click(screen.getByTitle('pim_common.cancel'));

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace');
});

test('it displays form errors when invalid data is submitted', async () => {
    mockFetchResponses({
        akeneo_connectivity_connection_custom_apps_rest_create: {
            json: {
                errors: [
                    {propertyPath: '', message: 'Limit Reached'},
                    {propertyPath: 'name', message: 'Invalid name'},
                    {propertyPath: 'activateUrl', message: 'Invalid activate url'},
                    {propertyPath: 'callbackUrl', message: 'Invalid callback url'},
                ],
            },
            status: 422,
        },
    });

    renderWithProviders(<CreateCustomAppPage />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.subtitle')
    ).toBeInTheDocument();

    assertItDisplaysTheForm();
    submitTheFormWith('', 'invalid', 'data');

    await waitFor(() => screen.queryByText('Invalid name'));

    assertItDoesNotDisplayCustomAppCredentials();
    assertItDisplaysTheForm();

    expect(screen.getByText('Limit Reached')).toBeInTheDocument();
    expect(screen.getByText('Invalid name')).toBeInTheDocument();
    expect(screen.getByText('Invalid activate url')).toBeInTheDocument();
    expect(screen.getByText('Invalid callback url')).toBeInTheDocument();

    expect(fetchMock).toBeCalledWith('akeneo_connectivity_connection_custom_apps_rest_create', {
        body: '{"name":"","activateUrl":"invalid","callbackUrl":"data"}',
        headers: {
            'Content-type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
    });
});

test('it redirects to the App store when modal is closed', () => {
    renderWithProviders(<CreateCustomAppPage />);

    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.subtitle')
    ).toBeInTheDocument();

    userEvent.click(screen.getByTitle('pim_common.cancel'));

    expect(historyMock.history.location.pathname).toBe('/akeneo_connectivity_connection_connect_marketplace');
});

const assertItDisplaysTheForm = () => {
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.title')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.description')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.link')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.name')
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.activate_url'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.callback_url'
        )
    ).toBeInTheDocument();
    expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeInTheDocument();
};

const submitTheFormWith = (name: string, activateUrl: string, callbackUrl: string) => {
    act(() => {
        userEvent.type(
            screen.getByPlaceholderText(
                'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.name'
            ),
            name
        );
        userEvent.type(
            screen.getByPlaceholderText(
                'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.activate_url'
            ),
            activateUrl
        );
        userEvent.type(
            screen.getByPlaceholderText(
                'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.callback_url'
            ),
            callbackUrl
        );

        userEvent.click(screen.getByText('pim_common.create'));
    });
};

const assertItDisplaysCustomAppCredentials = (clientId: string, secret: string) => {
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.title')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_id')
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_secret')
    ).toBeInTheDocument();
    expect(screen.getByText(clientId)).toBeInTheDocument();
    expect(screen.getByText(secret)).toBeInTheDocument();
    expect(screen.getByText('pim_common.done')).toBeInTheDocument();
};

const assertItDoesNotDisplayCustomAppCredentials = () => {
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.title')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_id')
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.custom_apps.create_modal.credentials.client_secret')
    ).not.toBeInTheDocument();
};
