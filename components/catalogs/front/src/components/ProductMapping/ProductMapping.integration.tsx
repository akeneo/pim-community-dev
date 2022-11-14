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
            url: '/rest/catalogs/123e4567-e89b-12d3-a456-426614174000',
            json: {
                id: '123e4567-e89b-12d3-a456-426614174000',
                product_mapping: {
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
                        source: 'description_html',
                        locale: 'en_US',
                        scope: 'ecommerce',
                    },
                },
                has_product_mapping_schema: true,
            },
        },
        {
            url: '/rest/catalogs/123e4567-e89b-12d3-a456-426614174000/mapping-schemas/product',
            json: {
                $id: 'https://example.com/product',
                $schema: 'https://api.akeneo.com/mapping/product/0.0.1/schema',
                $comment: 'My first schema !',
                title: 'Product Mapping',
                description: 'JSON Schema describing the structure of products expected by our application',
                type: 'object',
                properties: {
                    uuid: {
                        type: 'string',
                    },
                    name: {
                        type: 'string',
                    },
                    body_html: {
                        title: 'Description',
                        description: 'Product description in raw HTML',
                        type: 'string',
                    },
                },
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping catalogId={'123e4567-e89b-12d3-a456-426614174000'} />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.queryByTestId('product-mapping')).toBeInTheDocument();
    expect(await screen.findAllByText('UUID')).toHaveLength(2);
    expect(await screen.findByText('name')).toBeInTheDocument();
    expect(await screen.findByText('title')).toBeInTheDocument();
    expect(await screen.findByText('Description')).toBeInTheDocument();
});
