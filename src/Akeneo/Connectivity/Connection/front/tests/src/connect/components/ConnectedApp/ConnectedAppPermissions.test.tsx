import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import {PermissionsForm} from '@src/connect/components/PermissionsForm';
import useLoadPermissionsFormProviders from '@src/connect/hooks/use-load-permissions-form-providers';

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

jest.mock('@src/connect/hooks/use-load-permissions-form-providers', () => ({
    __esModule: true,
    default: jest.fn(() => [null, {}, jest.fn()]),
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

test('The connected app permissions renders with providers', () => {
    const mockedProviders = [
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
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA'],
            },
        },
    };

    (useLoadPermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppPermissions connectedApp={connectedApp} />);

    expect(PermissionsForm).toHaveBeenCalledTimes(2);

    expect(PermissionsForm).toHaveBeenNthCalledWith(
        1,
        expect.objectContaining({
            provider: mockedProviders[0],
            permissions: mockedPermissions.providerKey1,
        }),
        {}
    );
    expect(PermissionsForm).toHaveBeenNthCalledWith(
        2,
        expect.objectContaining({
            provider: mockedProviders[1],
            permissions: mockedPermissions.providerKey2,
        }),
        {}
    );
});

test('The connected app permissions renders without providers', () => {
    (useLoadPermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(<ConnectedAppPermissions connectedApp={connectedApp} />);

    expect(PermissionsForm).not.toHaveBeenCalled();
});
