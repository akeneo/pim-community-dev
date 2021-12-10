import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {Permissions} from '@src/connect/components/AppWizard/steps/Permissions';
import {renderWithProviders} from '../../../../test-utils';
import {PermissionsForm} from '@src/connect/components/PermissionsForm';

jest.mock('@src/connect/components/PermissionsForm', () => ({
    ...jest.requireActual('@src/connect/components/PermissionsForm'),
    PermissionsForm: jest.fn(() => null),
}));

test('The permissions step renders with no providers', () => {
    renderWithProviders(
        <Permissions appName='MyApp' providers={[]} setProviderPermissions={jest.fn()} permissions={{}} />
    );

    expect(PermissionsForm).not.toHaveBeenCalled();
});

test('The permissions step renders with providers from the registry', () => {
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
        <Permissions
            appName='MyApp'
            providers={mockedProviders}
            setProviderPermissions={jest.fn()}
            permissions={mockedPermissions}
        />
    );

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
