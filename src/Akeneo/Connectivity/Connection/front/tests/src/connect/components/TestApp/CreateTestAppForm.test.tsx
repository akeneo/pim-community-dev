import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {CreateTestAppForm} from '@src/connect/components/TestApp/CreateTestAppForm';
import userEvent from '@testing-library/user-event';

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

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
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.name.required'
        )
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.activate_url.required'
        )
    ).not.toBeInTheDocument();
    expect(
        screen.queryByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.callback_url.required'
        )
    ).not.toBeInTheDocument();
});

test('it displays errors when required fields are not fulfilled', () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    userEvent.click(screen.getByText('pim_common.create'));

    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.name.required'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.activate_url.required'
        )
    ).toBeInTheDocument();
    expect(
        screen.getByText(
            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.constraint.callback_url.required'
        )
    ).toBeInTheDocument();
});

test('it calls the onCancel props when the cancel button is clicked', () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    userEvent.click(screen.getByText('pim_common.cancel'));

    expect(onCancel).toHaveBeenCalled();
});

test('it sets credentials when the form is successfully submitted', () => {
    const onCancel = jest.fn();
    const setCredentials = jest.fn();

    renderWithProviders(<CreateTestAppForm onCancel={onCancel} setCredentials={setCredentials} />);

    userEvent.type(screen.getByTestId('name-input'), 'My TestApp name');
    userEvent.type(screen.getByTestId('activate-url-input'), 'https://example.com/activate-url');
    userEvent.type(screen.getByTestId('callback-url-input'), 'https://example.com/callback-url');
    userEvent.click(screen.getByText('pim_common.create'));

    expect(setCredentials).toHaveBeenCalledWith({
        clientId: 'clientId',
        clientSecret: 'clientSecret',
    });
});
