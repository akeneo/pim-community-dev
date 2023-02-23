import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {mockFetchResponses} from '../../../tests/mockFetchResponses';
import {ProductMapping} from './ProductMapping';

const SourcePanel = async () => await screen.findByTestId('source-panel');
const SourceSelectInput = async () => await screen.findByTestId('product-mapping-select-attribute');

const PRODUCT_MAPPING_SCHEMA = {
    properties: {
        uuid: {
            type: 'string',
        },
        name: {
            type: 'string',
            title: 'Product name',
            description: 'Name description',
            minLength: 3,
            maxLength: 50,
            pattern: '[a-zA-Z].',
        },
        weight: {
            type: 'number',
            description: 'Weight description',
            minimum: 0,
            maximum: 100,
        },
        size: {
            type: 'string',
            enum: ['S', 'M', 'L'],
        },
    },
    required: ['name'],
};

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
            url: '/rest/catalogs/attributes/ean',
            json: {
                code: 'ean',
                label: 'EAN',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/weight',
            json: {
                code: 'weight',
                label: 'Weight',
                type: 'pim_catalog_number',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/size',
            json: {
                code: 'size',
                label: 'Size',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [
                {
                    code: 'ecommerce',
                    label: 'Ecommerce',
                },
            ],
        },
        {
            url: '/rest/catalogs/channels/ecommerce',
            json: {
                code: 'ecommerce',
                label: 'Ecommerce',
            },
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
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
                    code: 'size',
                    label: 'Size',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
            ],
        },
        {
            url: '/rest/catalogs/attributes-by-target-type-and-target-format?page=1&limit=20&search=&targetType=number&targetFormat=',
            json: [
                {
                    code: 'weight',
                    label: 'Weight',
                    type: 'pim_catalog_number',
                    scopable: false,
                    localizable: false,
                },
            ],
        },
    ]);
});

test('it displays a list of targets and a description of each source', async () => {
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
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={{}}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('uuid')).toBeInTheDocument();
    expect(await screen.findByText('UUID')).toBeInTheDocument();
    expect(await screen.findByText('Product name')).toBeInTheDocument();
    expect(await screen.findByText('Product title')).toBeInTheDocument();
});

test('it displays a placeholder when no target has been selected', async () => {
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
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={{}}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(
        await within(await SourcePanel()).findByText('akeneo_catalogs.product_mapping.source.placeholder.title')
    ).toBeInTheDocument();
});

test('it displays error pills when the mapping is incorrect', async () => {
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
    };

    const errors = {
        name: {
            source: undefined,
            locale: undefined,
            scope: 'This channel must not be empty.',
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={errors}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByTestId('error-pill')).toBeInTheDocument();
});

test('it opens the source panel when a target is clicked', async () => {
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
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={{}}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('Product name'));
    expect(await within(await SourcePanel()).findByText('Product name')).toBeInTheDocument();
    expect(await within(await SourcePanel()).findByText('English (United States)')).toBeInTheDocument();
    expect(await within(await SourcePanel()).findByText('Ecommerce')).toBeInTheDocument();
});

test('it updates the state when a source changes', async () => {
    const onChange = jest.fn();

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
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={{}}
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('Product name'));
    fireEvent.mouseDown(await SourceSelectInput());
    fireEvent.click(await screen.findByText('EAN'));

    expect(onChange).toHaveBeenCalledWith({
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
        name: {
            source: 'ean',
            locale: null,
            scope: null,
        },
    });
});

test('it displays requirements', async () => {
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
        weight: {
            source: 'weight',
            locale: null,
            scope: null,
        },
        size: {
            source: 'size',
            locale: null,
            scope: null,
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    errors={{}}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.click(await screen.findByText('Product name'));
    expect(
        await within(await SourcePanel()).findByText('akeneo_catalogs.product_mapping.source.requirements.title')
    ).toBeInTheDocument();
    expect(await within(await SourcePanel()).findByText('Name description')).toBeInTheDocument();
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.minLength'
        )
    ).toBeInTheDocument();
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.maxLength'
        )
    ).toBeInTheDocument();
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.pattern'
        )
    ).toBeInTheDocument();

    fireEvent.click(await screen.findByText('weight'));
    expect(
        await within(await SourcePanel()).findByText('akeneo_catalogs.product_mapping.source.requirements.title')
    ).toBeInTheDocument();
    expect(await within(await SourcePanel()).findByText('Weight description')).toBeInTheDocument();
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.minimum'
        )
    ).toBeInTheDocument();
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.maximum'
        )
    ).toBeInTheDocument();

    fireEvent.click(await screen.findByText('size'));
    expect(
        await within(await SourcePanel()).findByText(
            'akeneo_catalogs.product_mapping.source.requirements.constraints.enum'
        )
    ).toBeInTheDocument();
});

test('it displays a pill for a required target', async () => {
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
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMappingSchema={PRODUCT_MAPPING_SCHEMA}
                    productMapping={productMapping}
                    errors={{}}
                    onChange={jest.fn()}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(
        await within(await screen.findByText('Product name')).findByTestId('required-pill')
    ).toBeInTheDocument();
});
