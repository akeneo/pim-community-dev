import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import fetchMock from 'jest-fetch-mock';
import {renderWithProviders, historyMock} from '../../../../test-utils';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import {PermissionsForm} from '@src/connect/components/PermissionsForm';

jest.mock('@src/connect/components/PermissionsForm', () => ({
    ...jest.requireActual('@src/connect/components/PermissionsForm'),
    PermissionsForm: jest.fn(() => null),
}));

beforeEach(() => {
    fetchMock.resetMocks();
    historyMock.reset();
    jest.clearAllMocks();
});

test('The connected app permissions tab renders with providers', () => {
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

    renderWithProviders(
        <ConnectedAppPermissions
            providers={mockedProviders}
            permissions={mockedPermissions}
            setProviderPermissions={jest.fn()}
            onlyDisplayViewPermissions={false}
        />
    );

    expect(PermissionsForm).toHaveBeenCalledTimes(2);

    expect(PermissionsForm).toHaveBeenNthCalledWith(
        1,
        expect.objectContaining({
            provider: mockedProviders[0],
            permissions: mockedPermissions.providerKey1,
            onlyDisplayViewPermissions: false,
        }),
        {}
    );
    expect(PermissionsForm).toHaveBeenNthCalledWith(
        2,
        expect.objectContaining({
            provider: mockedProviders[1],
            permissions: mockedPermissions.providerKey2,
            onlyDisplayViewPermissions: false,
        }),
        {}
    );
});

test('The connected app permissions can display only view permission', () => {
    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
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
    };

    renderWithProviders(
        <ConnectedAppPermissions
            providers={mockedProviders}
            permissions={mockedPermissions}
            setProviderPermissions={jest.fn()}
            onlyDisplayViewPermissions={true}
        />
    );

    expect(PermissionsForm).toHaveBeenCalledTimes(1);

    expect(PermissionsForm).toHaveBeenNthCalledWith(
        1,
        expect.objectContaining({
            provider: mockedProviders[0],
            permissions: mockedPermissions.providerKey1,
            onlyDisplayViewPermissions: true,
        }),
        {}
    );
});

test('The connected app permissions tab is not displayed when there is no providers', () => {
    renderWithProviders(
        <ConnectedAppPermissions
            providers={[]}
            permissions={{}}
            setProviderPermissions={jest.fn()}
            onlyDisplayViewPermissions={false}
        />
    );

    expect(PermissionsForm).not.toHaveBeenCalled();
});
