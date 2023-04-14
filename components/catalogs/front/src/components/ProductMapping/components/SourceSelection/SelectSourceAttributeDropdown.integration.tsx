import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Target} from '../../models/Target';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';
import {SelectSourceAttributeDropdown} from './SelectSourceAttributeDropdown';
import {QueryClient, QueryClientProvider} from 'react-query';

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Product title',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes-by-target-type-and-target-format?page=1&limit=20&search=&targetType=string&targetFormat=',
            json: [
                {
                    code: 'title',
                    label: 'Product title',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: true,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'description',
                    label: 'Description',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
            ],
        },
        {
            url: '/rest/catalogs/attributes-by-target-type-and-target-format?page=1&limit=20&search=&targetType=array%3Cstring%3E&targetFormat=',
            json: [],
        },
    ]);
});

test('it displays attributes', async () => {
    const target: Target = {
        code: 'name',
        label: 'name',
        type: 'string',
        format: null,
    };
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SelectSourceAttributeDropdown
                    target={target}
                    error={undefined}
                    onChange={jest.fn()}
                    selectedCode={'title'}
                ></SelectSourceAttributeDropdown>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('Product title')).toBeInTheDocument();
    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));
    expect(screen.queryByText('EAN')).toBeInTheDocument();
    expect(screen.queryByText('Description')).toBeInTheDocument();
});
test('it displays system attributes for string', async () => {
    const target: Target = {
        code: 'category',
        label: 'Category',
        type: 'string',
        format: null,
    };
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SelectSourceAttributeDropdown
                    target={target}
                    error={undefined}
                    onChange={jest.fn()}
                    selectedCode={''}
                ></SelectSourceAttributeDropdown>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));

    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.system_attributes.categories.label')
    ).toBeInTheDocument();
});

test('it displays system attributes for array of strings', async () => {
    const target: Target = {
        code: 'category',
        label: 'Category',
        type: 'array<string>',
        format: null,
    };
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SelectSourceAttributeDropdown
                    target={target}
                    error={undefined}
                    onChange={jest.fn()}
                    selectedCode={''}
                ></SelectSourceAttributeDropdown>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));

    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.system_attributes.categories.label')
    ).toBeInTheDocument();
});
