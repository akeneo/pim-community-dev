import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {wait} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import {PermissionsForm} from '@src/connect/components/PermissionsForm';
import {PermissionFormProvider} from '@src/shared/permission-form-registry';

let mockedProviders: PermissionFormProvider<any>[] = [];

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['scope 1'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
};

jest.mock('@src/shared/permission-form-registry', () => ({
    ...jest.requireActual('@src/shared/permission-form-registry'),
    usePermissionFormRegistry: jest.fn(() => {
        return {
            all: () => Promise.resolve(mockedProviders),
            count: jest.fn(),
        };
    }),
}));

jest.mock('@src/connect/components/PermissionsForm', () => ({
    ...jest.requireActual('@src/connect/components/PermissionsForm'),
    PermissionsForm: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app permissions renders with providers', async () => {
    mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];

    renderWithProviders(<ConnectedAppPermissions connectedApp={connectedApp} />);
    await wait(() => expect(PermissionsForm).toHaveBeenCalledTimes(2));

    expect(PermissionsForm).toHaveBeenNthCalledWith(
        1,
        expect.objectContaining({
            provider: mockedProviders[0],
        }),
        {}
    );
    expect(PermissionsForm).toHaveBeenNthCalledWith(
        2,
        expect.objectContaining({
            provider: mockedProviders[1],
        }),
        {}
    );
});

test('The connected app permissions renders without providers', async () => {
    mockedProviders = [];

    renderWithProviders(<ConnectedAppPermissions connectedApp={connectedApp} />);
    await wait(() => expect(PermissionsForm).not.toHaveBeenCalled());
});
