import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../../../test-utils';
import {ConnectedAppCatalogContainer} from '@src/connect/components/ConnectedApp/Catalog/ConnectedAppCatalogContainer';
import userEvent from '@testing-library/user-event';
import {useCatalogForm} from '@akeneo-pim-community/catalogs';
import {mocked} from 'ts-jest/utils';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';

beforeEach(() => {
    jest.clearAllMocks();
});

jest.mock('@akeneo-pim-community/catalogs', () => ({
    CatalogEdit: () => {
        return <div>[Catalog Edit]</div>;
    },
    useCatalogForm: jest.fn(() => [{}, jest.fn(), false]),
}));

test('The catalog container renders', () => {
    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['read_products', 'write_products'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'connection_username',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: true,
    };

    const catalog = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Store FR',
        enabled: true,
        owner_username: 'willy',
    };

    renderWithProviders(<ConnectedAppCatalogContainer connectedApp={connectedApp} catalog={catalog} />);

    expect(screen.getByText('App A')).toBeInTheDocument();
    expect(screen.getAllByText('Store FR')).toHaveLength(2);
    expect(screen.getByText('[Catalog Edit]')).toBeInTheDocument();
    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();
});

test('The save button click triggers save', async () => {
    const save = jest.fn().mockResolvedValue(true);
    const notify = jest.fn();

    renderCatalogContainerAndSave(save, notify);

    expect(save).toHaveBeenCalled();

    await waitFor(() => {
        expect(notify).toBeCalledWith(
            NotificationLevel.SUCCESS,
            'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.success'
        );
    });
});

test('The save button click triggers save that results in a user error', async () => {
    const save = jest.fn().mockResolvedValue(false);
    const notify = jest.fn();

    renderCatalogContainerAndSave(save, notify);

    expect(save).toHaveBeenCalled();

    await waitFor(() => {
        expect(notify).toBeCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.error'
        );
    });
});

test('The save button click triggers save that results in a server error', async () => {
    const save = jest.fn().mockRejectedValue('Some error');
    const notify = jest.fn();

    renderCatalogContainerAndSave(save, notify);

    expect(save).toHaveBeenCalled();

    await waitFor(() => {
        expect(notify).toBeCalledWith(
            NotificationLevel.ERROR,
            'akeneo_connectivity.connection.connect.connected_apps.edit.catalogs.edit.flash.unknown_error'
        );
    });
});

function renderCatalogContainerAndSave(saveMock: jest.Mock, notifyMock: jest.Mock) {
    mocked(useCatalogForm).mockImplementation(() => [
        {
            values: {},
            dispatch: jest.fn(),
            errors: {},
        },
        saveMock,
        true,
    ]);

    const connectedApp = {
        id: '12345',
        name: 'App A',
        scopes: ['read_products', 'write_products'],
        connection_code: 'some_connection_code',
        logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
        author: 'Author A',
        user_group_name: 'app_123456abcde',
        connection_username: 'connection_username',
        categories: ['e-commerce', 'print'],
        certified: false,
        partner: null,
        is_custom_app: false,
        is_pending: false,
        has_outdated_scopes: true,
    };

    const catalog = {
        id: '123e4567-e89b-12d3-a456-426614174000',
        name: 'Store FR',
        enabled: true,
        owner_username: 'willy',
    };

    renderWithProviders(
        <NotifyContext.Provider value={notifyMock}>
            <ConnectedAppCatalogContainer connectedApp={connectedApp} catalog={catalog} />
        </NotifyContext.Provider>
    );

    act(() => userEvent.click(screen.getByText('pim_common.save')));
}
