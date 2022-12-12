import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {mockFetchResponses} from '../../../tests/mockFetchResponses';
import {ProductMapping} from './ProductMapping';

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

const clickOnMappingTarget = async (mappingTarget: string) => {
    expect(await screen.findByText(mappingTarget)).toBeInTheDocument();
    fireEvent.click(await screen.findByText(mappingTarget));
    expect(await screen.findByText('akeneo_catalogs.product_mapping.source.title')).toBeInTheDocument();
};

const selectAttributeAsSource = async (attributeName: string) => {
    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));
    expect(await screen.findByTitle('akeneo_catalogs.product_mapping.source.select_source.search')).toBeInTheDocument();
    fireEvent.click(await screen.findByText(attributeName));
};

const selectSourceLocale = async (locale: string) => {
    expect(
        await screen.findByText('akeneo_catalogs.product_mapping.source.parameters.locale.label')
    ).toBeInTheDocument();
    openDropdown('source-parameter-locale-dropdown');
    fireEvent.click(await screen.findByText(locale));
};

const selectSourceChannel = async (channel: string) => {
    expect(
        await screen.findByText('akeneo_catalogs.product_mapping.source.parameters.channel.label')
    ).toBeInTheDocument();
    openDropdown('source-parameter-channel-dropdown');
    fireEvent.click(await screen.findByText(channel));
};

const assertLocaleSourceParameterIsNotDisplayed = () => {
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.parameters.locale.label')
    ).not.toBeInTheDocument();
};

const assertLocaleSourceParameterIsDisplayed = () => {
    expect(screen.queryByText('akeneo_catalogs.product_mapping.source.parameters.locale.label')).toBeInTheDocument();
};

const assertChannelSourceParameterIsNotDisplayed = () => {
    expect(
        screen.queryByText('akeneo_catalogs.product_mapping.source.parameters.channel.label')
    ).not.toBeInTheDocument();
};

const localesPayload = {
    url: '/rest/catalogs/locales?page=1&limit=20',
    json: [
        {
            code: 'de_DE',
            label: 'German (Germany)',
        },
        {
            code: 'en_US',
            label: 'English (United States)',
        },
        {
            code: 'fr_FR',
            label: 'French (France)',
        },
    ],
};

const channelsPayload = {
    url: '/rest/catalogs/channels?page=1&limit=20',
    json: [
        {
            code: 'mobile',
            label: 'Mobile',
        },
        {
            code: 'print',
            label: 'Print',
        },
        {
            code: 'ecommerce',
            label: 'Ecommerce',
        },
    ],
};

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
                title: 'Uuid',
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
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.queryByTestId('product-mapping')).toBeInTheDocument();

    expect(await screen.findByText('Uuid')).toBeInTheDocument();
    expect(await screen.findByText('UUID')).toBeInTheDocument();

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
            source: undefined,
            locale: undefined,
            scope: undefined,
        },
        name: {
            source: undefined,
            locale: undefined,
            scope: 'This channel must be empty.',
        },
        body_html: {
            source: undefined,
            locale: 'This locale must not be empty.',
            scope: undefined,
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findAllByTestId('error-pill')).toHaveLength(2);
});

test('it displays a placeholder when uuid target is clicked', async () => {
    const onChange = jest.fn();

    const productMapping = {
        uuid: {
            source: 'uuid',
            locale: null,
            scope: null,
        },
    };

    const productMappingSchema = {
        properties: {
            uuid: {
                title: 'Uuid',
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(await screen.findByText('Uuid')).toBeInTheDocument();
    fireEvent.click(await screen.findByText('Uuid'));

    expect(
        await screen.findByText('akeneo_catalogs.product_mapping.source.uuid_placeholder.illustration_title')
    ).toBeInTheDocument();
    expect(
        await screen.findByText('akeneo_catalogs.product_mapping.source.uuid_placeholder.subtitle')
    ).toBeInTheDocument();
    expect(await screen.findByText('akeneo_catalogs.product_mapping.source.uuid_placeholder.link')).toBeInTheDocument();
    expect(screen.queryByText('akeneo_catalogs.product_mapping.source.title')).not.toBeInTheDocument();
    expect(screen.queryByText('akeneo_catalogs.product_mapping.source.select_source.search')).not.toBeInTheDocument();
    assertChannelSourceParameterIsNotDisplayed();
    assertLocaleSourceParameterIsNotDisplayed();
});

test('it updates the state when a source is selected', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/variation_name',
            json: {
                code: 'variation_name',
                label: 'Variant Name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'name',
                    label: 'Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'variation_name',
                    label: 'Variant Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
            ],
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('pim erp name');
    await selectAttributeAsSource('Variant Name');

    assertChannelSourceParameterIsNotDisplayed();
    assertLocaleSourceParameterIsNotDisplayed();

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

test('it updates the state when a channel is selected for an attribute with value per channel', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        localesPayload,
        channelsPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/variation_name',
            json: {
                code: 'variation_name',
                label: 'Variation name',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'name',
                    label: 'Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'variation_name',
                    label: 'Variant Name',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: false,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
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
            locale: null,
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('pim erp name');
    await selectAttributeAsSource('Variant Name');

    assertLocaleSourceParameterIsNotDisplayed();
    await selectSourceChannel('Ecommerce');

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
            scope: 'ecommerce',
        },
    });
});

test('it updates the state when a locale is selected for an attribute with value per locale', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        localesPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/variation_name',
            json: {
                code: 'variation_name',
                label: 'Variation name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'name',
                    label: 'Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'variation_name',
                    label: 'Variant Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: true,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
            ],
        },
        {
            url: '/rest/catalogs/locales?codes=en_US',
            json: [
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
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
            locale: 'fr_FR',
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('pim erp name');
    await selectAttributeAsSource('Variant Name');

    assertChannelSourceParameterIsNotDisplayed();
    await selectSourceLocale('English (United States)');

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
            locale: 'en_US',
            scope: null,
        },
    });
});

test('it updates the state when a locale and channel is selected for an attribute with value per locale and per channel', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        localesPayload,
        channelsPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/variation_name',
            json: {
                code: 'variation_name',
                label: 'Variation name',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'name',
                    label: 'Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'variation_name',
                    label: 'Variant Name',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: true,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: true,
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
                    code: 'fr_FR',
                    label: 'French (France)',
                },
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
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
            locale: null,
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('pim erp name');
    await selectAttributeAsSource('Variant Name');

    assertLocaleSourceParameterIsNotDisplayed();
    await selectSourceChannel('Ecommerce');
    assertLocaleSourceParameterIsDisplayed();
    await selectSourceLocale('English (United States)');

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
            locale: 'en_US',
            scope: 'ecommerce',
        },
    });
});

test('it resets source locale when channel changes for an attribute with value per channel and per locale', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        localesPayload,
        channelsPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/erp_name',
            json: {
                code: 'erp_name',
                label: 'pim erp name',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes/variation_name',
            json: {
                code: 'variation_name',
                label: 'Variation name',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'name',
                    label: 'Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
                {
                    code: 'variation_name',
                    label: 'Variant Name',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: true,
                },
                {
                    code: 'ean',
                    label: 'EAN',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: true,
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
            url: '/rest/catalogs/channels/mobile',
            json: {
                code: 'mobile',
                label: 'Mobile',
            },
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [
                {
                    code: 'fr_FR',
                    label: 'French (France)',
                },
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
        },
        {
            url: '/rest/catalogs/channels/mobile/locales',
            json: [
                {
                    code: 'de_DE',
                    label: 'German (Germany)',
                },
            ],
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
            locale: null,
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
                    onChange={onChange}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('pim erp name');
    await selectAttributeAsSource('Variant Name');
    await selectSourceChannel('Ecommerce');
    await selectSourceLocale('English (United States)');

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
            locale: 'en_US',
            scope: 'ecommerce',
        },
    });

    await selectSourceChannel('Mobile');

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
            scope: 'mobile',
        },
    });
});

test('it displays error message when source attribute is incorrect', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'title',
                    label: 'Title',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: false,
                },
            ],
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
        },
    };

    const mappingErrors = {
        name: {
            source: 'Source error',
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('name');
    expect(await screen.findByText('Source error')).toBeInTheDocument();
});

test('it displays error message when source scope is incorrect', async () => {
    mockFetchResponses([
        channelsPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: false,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'title',
                    label: 'Title',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: false,
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
    };

    const productMappingSchema = {
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
            },
        },
    };

    const mappingErrors = {
        name: {
            scope: 'Scope error',
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('name');
    expect(await screen.findByText('Scope error')).toBeInTheDocument();
});
test('it displays error message when source local but not scopable is incorrect', async () => {
    mockFetchResponses([
        localesPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: false,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'title',
                    label: 'Title',
                    type: 'pim_catalog_text',
                    scopable: false,
                    localizable: true,
                },
            ],
        },
        {
            url: '/rest/catalogs/locales?codes=en_US',
            json: [
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
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
        },
    };

    const mappingErrors = {
        name: {
            locale: 'Locale error',
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('name');
    expect(await screen.findByText('Locale error')).toBeInTheDocument();
});

test('it displays error message when source local and scopable is incorrect', async () => {
    mockFetchResponses([
        localesPayload,
        channelsPayload,
        {
            url: '/rest/catalogs/attributes/title',
            json: {
                code: 'title',
                label: 'Title',
                type: 'pim_catalog_text',
                scopable: true,
                localizable: true,
            },
        },
        {
            url: '/rest/catalogs/attributes?page=1&limit=20&search=&types=text',
            json: [
                {
                    code: 'title',
                    label: 'Title',
                    type: 'pim_catalog_text',
                    scopable: true,
                    localizable: true,
                },
            ],
        },
        {
            url: '/rest/catalogs/locales?codes=en_US',
            json: [
                {
                    code: 'en_US',
                    label: 'English (United States)',
                },
            ],
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [
                {
                    code: 'fr_FR',
                    label: 'French (France)',
                },
                {
                    code: 'en_US',
                    label: 'English (United States)',
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
    };

    const productMappingSchema = {
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
            },
        },
    };

    const mappingErrors = {
        name: {
            locale: 'Locale error',
        },
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <ProductMapping
                    productMapping={productMapping}
                    productMappingSchema={productMappingSchema}
                    errors={mappingErrors}
                    onChange={() => null}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await clickOnMappingTarget('name');
    expect(await screen.findByText('Locale error')).toBeInTheDocument();
});
