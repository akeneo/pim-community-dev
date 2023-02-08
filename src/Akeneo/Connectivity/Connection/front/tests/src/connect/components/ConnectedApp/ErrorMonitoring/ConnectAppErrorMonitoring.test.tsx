import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {ConnectedAppErrorMonitoring} from '@src/connect/components/ConnectedApp/ErrorMonitoring/ConnectedAppErrorMonitoring';
import {renderWithProviders} from '../../../../../test-utils';
import {useFetchConnectedAppMonitoringSettings} from '@src/connect/hooks/use-fetch-connected-app-monitoring-settings';
import {ConnectionErrors} from '@src/error-management/components/ConnectionErrors';
import fetchMock from 'jest-fetch-mock';
import {waitFor} from '@testing-library/react';
import {NotDataSourceConnectedApp} from '@src/connect/components/ConnectedApp/ErrorMonitoring/NotDataSourceConnectedApp';
import {NotAuditableConnectedApp} from '@src/connect/components/ConnectedApp/ErrorMonitoring/NotAuditableConnectedApp';
import {ErrorMonitoringError} from '@src/connect/components/ConnectedApp/ErrorMonitoring/ErrorMonitoringError';

beforeEach(() => {
    fetchMock.resetMocks();
    jest.clearAllMocks();
});

jest.mock('@src/connect/hooks/use-fetch-connected-app-monitoring-settings', () => ({
    ...jest.requireActual('@src/connect/hooks/use-fetch-connected-app-monitoring-settings'),
    useFetchConnectedAppMonitoringSettings: jest.fn().mockImplementation(() => () => Promise.resolve()),
}));
jest.mock('@src/error-management/components/ConnectionErrors', () => ({
    ...jest.requireActual('@src/error-management/components/ConnectionErrors'),
    ConnectionErrors: jest.fn(() => null),
}));
jest.mock('@src/connect/components/ConnectedApp/ErrorMonitoring/NotDataSourceConnectedApp', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ErrorMonitoring/NotDataSourceConnectedApp'),
    NotDataSourceConnectedApp: jest.fn(() => null),
}));
jest.mock('@src/connect/components/ConnectedApp/ErrorMonitoring/NotAuditableConnectedApp', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ErrorMonitoring/NotAuditableConnectedApp'),
    NotAuditableConnectedApp: jest.fn(() => null),
}));
jest.mock('@src/connect/components/ConnectedApp/ErrorMonitoring/ErrorMonitoringError', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ErrorMonitoring/ErrorMonitoringError'),
    ErrorMonitoringError: jest.fn(() => null),
}));

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['scope1', 'scope2'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    connection_username: 'Connection Username',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
    is_custom_app: false,
    is_pending: false,
    has_outdated_scopes: false,
};

test('It renders the app errors', async done => {
    (useFetchConnectedAppMonitoringSettings as jest.Mock).mockImplementation(
        () => () =>
            Promise.resolve({
                flowType: 'data_source',
                auditable: true,
            })
    );

    renderWithProviders(<ConnectedAppErrorMonitoring connectedApp={connectedApp} />);

    await waitFor(() =>
        expect(ConnectionErrors).toHaveBeenCalledWith(
            {
                connectionCode: connectedApp.connection_code,
                description:
                    'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.helper.description',
            },
            {}
        )
    );
    done();
});

test('It renders the good illustration if the app is not data source', async done => {
    (useFetchConnectedAppMonitoringSettings as jest.Mock).mockImplementation(
        () => () =>
            Promise.resolve({
                flowType: 'data_destination',
                auditable: true,
            })
    );
    renderWithProviders(<ConnectedAppErrorMonitoring connectedApp={connectedApp} />);

    await waitFor(() => expect(NotDataSourceConnectedApp).toHaveBeenCalled());
    done();
});

test('It renders the good illustration if the app is not auditable', async done => {
    (useFetchConnectedAppMonitoringSettings as jest.Mock).mockImplementation(
        () => () =>
            Promise.resolve({
                flowType: 'data_source',
                auditable: false,
            })
    );
    renderWithProviders(<ConnectedAppErrorMonitoring connectedApp={connectedApp} />);

    await waitFor(() => expect(NotAuditableConnectedApp).toHaveBeenCalled());
    done();
});

test('It renders the good illustration if something went wrong when fetching monitoring setting', async done => {
    (useFetchConnectedAppMonitoringSettings as jest.Mock).mockImplementation(() => () => Promise.reject());
    renderWithProviders(<ConnectedAppErrorMonitoring connectedApp={connectedApp} />);

    await waitFor(() => expect(ErrorMonitoringError).toHaveBeenCalled());
    done();
});
