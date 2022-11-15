import React from 'react';
import {render, screen} from '@testing-library/react';
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
            uuid: {},
            name: {},
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
                <ProductMapping productMapping={productMapping} productMappingSchema={productMappingSchema} />
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
