import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '../../../../../test-utils';
import {ConnectedAppCatalogContainer} from '@src/connect/components/ConnectedApp/Catalog/ConnectedAppCatalogContainer';
import userEvent from '@testing-library/user-event';
import {useCatalogForm} from '@akeneo-pim-community/catalogs';
import {mocked} from 'ts-jest/utils';

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
        is_test_app: false,
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

test('The save button click triggers save', () => {
    const save = jest.fn();

    mocked(useCatalogForm).mockImplementation(() => [
        {
            values: {},
            dispatch: jest.fn(),
            errors: {},
        },
        save,
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
        is_test_app: false,
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

    act(() => userEvent.click(screen.getByText('pim_common.save')));

    expect(save).toHaveBeenCalled();
});
