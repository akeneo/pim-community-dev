import {fireEvent, render, screen, within} from '@testing-library/react';
import {mockFetchResponses} from '../../../../../../tests/mockFetchResponses';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import React from 'react';
import {AssetAttributeSourceSelection} from './AssetAttributeSourceSelection';

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

const openDropdown = async (selector: string): Promise<void> => {
    const container = await screen.findByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

test('it calls onChange when the asset attribute changes', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/locales?codes=en_US',
            json: EN,
        },
        {
            url: '/rest/catalogs/locales?page=1&limit=20',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=brands&targetType=array%3Cstring%3E&targetFormat=',
            json: [
                {
                    identifier: 'brand_label',
                    label: 'Label',
                    type: 'text',
                    scopable: false,
                    localizable: true,
                },
                {
                    identifier: 'brand_company',
                    label: 'Company',
                    type: 'text',
                    scopable: false,
                    localizable: false,
                },
            ],
        },
        {
            url: '/rest/catalogs/asset-attributes/brand_label',
            json: {
                identifier: 'brand_label',
                label: 'Label',
                type: 'text',
                scopable: false,
                localizable: true,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <AssetAttributeSourceSelection
                    source={{
                        source: 'brand_attribute',
                        locale: null,
                        scope: null,
                        parameters: {
                            sub_source: 'brand_label',
                            sub_scope: null,
                            sub_locale: 'en_US',
                        }
                    }}
                    target={{
                        code: 'brands_list',
                        label: 'Brands List',
                        type: 'array<string>',
                        format: null,
                    }}
                    errors={null}
                    onChange={onChange}
                    assetFamilyIdentifier={'brands'}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.mouseDown(await screen.findByTestId('asset-attributes-dropdown'));

    expect(await screen.findByText('Company')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Company'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'brand_attribute',
        locale: null,
        scope: null,
        parameters: {
            sub_source: 'brand_company',
            sub_scope: null,
            sub_locale: null,
        }
    });
});

test('it calls onChange when the asset attribute channel changes', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=brands&targetType=array%3Cstring%3E&targetFormat=',
            json: [
                {
                    identifier: 'brand_label',
                    label: 'Label',
                    type: 'text',
                    scopable: true,
                    localizable: true,
                },
            ],
        },
        {
            url: '/rest/catalogs/asset-attributes/brand_label',
            json: {
                identifier: 'brand_label',
                label: 'Label',
                type: 'text',
                scopable: true,
                localizable: true,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <AssetAttributeSourceSelection
                    source={{
                        source: 'brand_attribute',
                        locale: null,
                        scope: null,
                        parameters: {
                            sub_source: 'brand_label',
                            sub_scope: null,
                            sub_locale: null,
                        }
                    }}
                    target={{
                        code: 'brands_list',
                        label: 'Brands List',
                        type: 'array<string>',
                        format: null,
                    }}
                    errors={null}
                    onChange={onChange}
                    assetFamilyIdentifier={'brands'}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await openDropdown('asset-attribute-channel-dropdown');

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();

    fireEvent.click(screen.getByText('E-commerce'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'brand_attribute',
        locale: null,
        scope: null,
        parameters: {
            sub_source: 'brand_label',
            sub_scope: 'ecommerce',
            sub_locale: null,
        }
    });
});

test('it calls onChange when the asset attribute locale changes', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels/ecommerce',
            json: ECOMMERCE,
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=brands&targetType=array%3Cstring%3E&targetFormat=',
            json: [
                {
                    identifier: 'brand_label',
                    label: 'Label',
                    type: 'text',
                    scopable: true,
                    localizable: true,
                },
            ],
        },
        {
            url: '/rest/catalogs/asset-attributes/brand_label',
            json: {
                identifier: 'brand_label',
                label: 'Label',
                type: 'text',
                scopable: true,
                localizable: true,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <AssetAttributeSourceSelection
                    source={{
                        source: 'brand_attribute',
                        locale: null,
                        scope: null,
                        parameters: {
                            sub_source: 'brand_label',
                            sub_scope: 'ecommerce',
                            sub_locale: null,
                        }
                    }}
                    target={{
                        code: 'brands_list',
                        label: 'Brands List',
                        type: 'array<string>',
                        format: null,
                    }}
                    errors={null}
                    onChange={onChange}
                    assetFamilyIdentifier={'brands'}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await openDropdown('asset-attribute-channel-locale-dropdown');

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();

    fireEvent.click(screen.getByText('French'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'brand_attribute',
        scope: null,
        locale: null,
        parameters: {
            sub_source: 'brand_label',
            sub_scope: 'ecommerce',
            sub_locale: 'fr_FR',
        }
    });
});

test('it calls onChange when the asset attribute locale changes with a non scopable asset attribute', async () => {
    const onChange = jest.fn();

        mockFetchResponses([
            {
                url: '/rest/catalogs/locales?page=1&limit=20',
                json: [EN, FR, DE],
            },
        {
            url: '/rest/catalogs/asset-attributes-by-target-type-and-target-format?assetFamilyIdentifier=brands&targetType=array%3Cstring%3E&targetFormat=',
            json: [
                {
                    identifier: 'brand_label',
                    label: 'Label',
                    type: 'text',
                    scopable: false,
                    localizable: true,
                },
            ],
        },
        {
            url: '/rest/catalogs/asset-attributes/brand_label',
            json: {
                identifier: 'brand_label',
                label: 'Label',
                type: 'text',
                scopable: false,
                localizable: true,
            },
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <AssetAttributeSourceSelection
                    source={{
                        source: 'brand_attribute',
                        locale: null,
                        scope: null,
                        parameters: {
                            sub_source: 'brand_label',
                            sub_scope: null,
                            sub_locale: null,
                        }
                    }}
                    target={{
                        code: 'brands_list',
                        label: 'Brands List',
                        type: 'array<string>',
                        format: null,
                    }}
                    errors={null}
                    onChange={onChange}
                    assetFamilyIdentifier={'brands'}
                />
            </QueryClientProvider>
        </ThemeProvider>
    );

    await openDropdown('asset-attribute-locale-dropdown');

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();

    fireEvent.click(screen.getByText('French'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'brand_attribute',
        scope: null,
        locale: null,
        parameters: {
            sub_source: 'brand_label',
            sub_scope: null,
            sub_locale: 'fr_FR',
        }
    });
});
