import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import isGrantedOnProduct from '@src/connect/is-granted-on-product';

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: [],
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

test('it defines if the connected app is allowed to view products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure', 'read_products', 'write_products'],
            },
            'view'
        )
    ).toEqual(true);
});

test('it defines if the connected app is not allowed to view products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure'],
            },
            'view'
        )
    ).toEqual(false);
});

test('it defines if the connected app is allowed to edit products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure', 'read_products', 'write_products'],
            },
            'edit'
        )
    ).toEqual(true);
});

test('it defines if the connected app is not allowed to edit products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure', 'read_products'],
            },
            'edit'
        )
    ).toEqual(false);
});

test('it defines if the connected app is allowed to delete products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure', 'read_products', 'write_products', 'delete_products'],
            },
            'delete'
        )
    ).toEqual(true);
});

test('it defines if the connected app is not allowed to delete products', () => {
    expect(
        isGrantedOnProduct(
            {
                ...connectedApp,
                scopes: ['read_catalog_structure', 'read_products', 'write_products'],
            },
            'delete'
        )
    ).toEqual(false);
});
