import React, {forwardRef} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../../../test-utils';
import {CatalogEdit, CatalogEditRef} from '@akeneo-pim-community/catalogs';
import {ConnectedAppCatalogContainer} from '@src/connect/components/ConnectedApp/Catalog/ConnectedAppCatalogContainer';

beforeEach(() => {
    jest.clearAllMocks();
});

jest.mock('@akeneo-pim-community/catalogs', () => ({
    ...jest.requireActual('@akeneo-pim-community/catalogs'),
    CatalogEdit: jest.fn(
        forwardRef<CatalogEditRef>(() => {
            return <>Catalog Edit</>;
        })
    ),
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

    expect(screen.queryByText('App A')).toBeInTheDocument();
    expect(screen.queryAllByText('Store FR')).toHaveLength(2);

    expect(CatalogEdit).toHaveBeenCalledWith(
        expect.objectContaining({
            id: '123e4567-e89b-12d3-a456-426614174000',
        }),
        {}
    );
});
