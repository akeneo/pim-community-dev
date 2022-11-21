import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {mockFetchResponses} from '../../../tests/mockFetchResponses';
import {ProductMapping} from './ProductMapping';

test('it displays an existing product mapping', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
            },
        },
    ]);

    const productMapping = {
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
        name: {
            source: 'title',
            locale: 'en_US',
            scope: 'ecommerce',
        },
        body_html: {
            source: null,
            locale: 'en_US',
            scope: 'ecommerce',
        },
        erp_name: {
            source: 'erp_name',
            locale: 'en_US',
            scope: null,
        },
    };

    const productMappingSchema = {
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
            },
            body_html: {
                title: 'Description',
                type: 'string',
            },
            erp_name: {
                title: 'ERP',
                type: 'string',
            },
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={{}}
                    onChange={(values) => {}}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.queryByTestId('product-mapping')).toBeInTheDocument();
    expect(await screen.findAllByText('UUID')).toHaveLength(2);

    expect(await screen.findByText('name')).toBeInTheDocument();
    expect(await screen.findByText('Title')).toBeInTheDocument();

    expect(await screen.findByText('Description')).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_mapping.target.table.placeholder')).toBeInTheDocument();

    expect(await screen.findByText('ERP')).toBeInTheDocument();
    expect(await screen.findByText('pim erp name')).toBeInTheDocument();
});

test('it displays error pills when mapping is incorrect', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
            },
        },
        {
            url: '/rest/catalogs/attributes/description',
            json: {
                code: 'description',
                label: 'Description HTML',
            },
        },
    ]);

    const productMapping = {
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
        name: {
            source: 'title',
            locale: null,
            scope: 'ecommerce',
        },
        body_html: {
            source: 'description',
            locale: null,
            scope: 'ecommerce',
        },
    };

    const productMappingSchema = {
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
            },
            body_html: {
                type: 'string',
            },
        },
    };

    const mappingErrors = {
        uuid: {
            source: null,
            locale: null,
            scope: null,
        },
        name: {
            source: null,
            locale: null,
            scope: 'This channel must be empty.',
        },
        body_html: {
            source: null,
            locale: 'This locale must not be empty.',
            scope: null,
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={(values) => {}}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findAllByTestId('error-pill')).toHaveLength(2);
});


test('it updates the state when a source is selected', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    "code":"name",
                    "label":"Name",
                    "type":"pim_catalog_text",
                    "scopable":false,
                    "localizable":false
                },
                {
                    "code":"variation_name",
                    "label":"Variant Name",
                    "type":"pim_catalog_text",
                    "scopable":false,
                    "localizable":false
                },
                {
                    "code":"ean",
                    "label":"EAN",
                    "type":"pim_catalog_text",
                    "scopable":false,
                    "localizable":false
                },
            ]
        },
    ]);

    const productMapping = {
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
        name: {
            source: 'title',
            locale: 'en_US',
            scope: 'ecommerce',
        },
        body_html: {
            source: null,
            locale: 'en_US',
            scope: 'ecommerce',
        },
        erp_name: {
            source: 'erp_name',
            locale: 'en_US',
            scope: null,
        },
    };

    const productMappingSchema = {
        properties: {
            uuid: {
            },
            name: {
            },
            body_html: {
                title: 'Description',
            },
            erp_name: {
                title: 'ERP',
            },
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={{}}
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('pim erp name')).toBeInTheDocument();
    fireEvent.click(await screen.findByText('pim erp name'));
    expect(await screen.findByText('akeneo_catalogs.product_mapping.source.title')).toBeInTheDocument();
    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));
    expect(await screen.findByTitle('akeneo_catalogs.product_selection.add_criteria.search')).toBeInTheDocument();
    fireEvent.click(await screen.findByText('Variant Name'));

    expect(onChange).toHaveBeenCalledWith({
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
        name: {
            source: 'title',
            locale: 'en_US',
            scope: 'ecommerce',
        },
        body_html: {
            source: null,
            locale: 'en_US',
            scope: 'ecommerce',
        },
        erp_name: {
            source: 'variation_name',
            locale: null,
            scope: null,
        },
    });
});
