import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock, mockFetchResponses} from '../../../../test-utils';
import {CreateTestAppForm} from '@src/connect/components/TestApp/CreateTestAppForm';
import userEvent from '@testing-library/user-event';
import {useCreateTestApp} from '@src/connect/hooks/use-create-test-app';
import {act} from '@testing-library/react-hooks';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

jest.mock('@src/connect/hooks/use-create-test-app', () => ({
    ...jest.requireActual('@src/connect/hooks/use-create-test-app'),
    useCreateTestApp: jest.fn(
        () => () =>
            Promise.resolve({
                ok: true,
                json: () =>
                    Promise.resolve({
                        clientId: 'client_id',
                        clientSecret: 'client_secret',
                    }),
            })
    ),
}));

test('it displays the form', () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.title')
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.description'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.link')
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.name'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.activate_url'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.callback_url'
        )
    ).toBeInTheDocument();
    expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();
    expect(screen.getByText('pim_common.create')).toBeInTheDocument();

    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.errors.creation.name.not_blank'
        )
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.errors.creation.activate_url.not_blank'
        )
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.errors.creation.callback_url.not_blank'
        )
    ).not.toBeInTheDocument();
});

test('it displays errors when not_blank fields are not fulfilled', async () => {
    (useCreateTestApp as jest.Mock).mockImplementationOnce(
        () => () =>
            Promise.resolve({
                ok: false,
                json: () =>
                    Promise.resolve({
                        errors: [
                            {
                                propertyPath: 'name',
                                message: 'name.not_blank',
                            },
                            {
                                propertyPath: 'activateUrl',
                                message: 'activate_url.not_blank',
                            },
                            {
                                propertyPath: 'callbackUrl',
                                message: 'callback_url.not_blank',
                            },
                        ],
                    }),
            })
    );

    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    act(() => {
        userEvent.click(screen.getByText('pim_common.create'));
    });

    expect(await screen.findByText('name.not_blank')).toBeInTheDocument();
    expect(await screen.findByText('activate_url.not_blank')).toBeInTheDocument();
    expect(await screen.findByText('callback_url.not_blank')).toBeInTheDocument();
});

test('it calls the onCancel props when the cancel button is clicked', () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    act(() => {
        userEvent.click(screen.getByText('pim_common.cancel'));
    });

    expect(onCancel).toHaveBeenCalled();
});

test('it sets credentials when the form is successfully submitted', async () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    userEvent.type(screen.getByTestId('name-input'), 'My TestApp name');
    userEvent.type(screen.getByTestId('activate-url-input'), 'https://example.com/activate-url');
    userEvent.type(screen.getByTestId('callback-url-input'), 'https://example.com/callback-url');

    act(() => {
        userEvent.click(screen.getByText('pim_common.create'));
    });

    await waitFor(() =>
        expect(setCredentials).toHaveBeenCalledWith({
            clientId: 'client_id',
            clientSecret: 'client_secret',
        })
    );
});
