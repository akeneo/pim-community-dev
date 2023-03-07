import React from 'react';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {SourceSettings} from './SourceSettings';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

test('it call onChange when the channel change', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [EN, FR, DE],
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourceSettings
                    source={{source: 'description', locale: null, scope: null}}
                    attribute={{
                        label: 'Description',
                        code: 'description',
                        type: 'text',
                        scopable: true,
                        localizable: true,
                        attribute_group_code: '',
                        attribute_group_label: '',
                    }}
                    onChange={onChange}
                    errors={null}
                ></SourceSettings>
            </QueryClientProvider>
        </ThemeProvider>
    );

    openDropdown('source-parameter-channel-dropdown');

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();

    fireEvent.click(screen.getByText('E-commerce'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'description',
        locale: null,
        scope: 'ecommerce',
    });
});

test('it call onChange when the locale change', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels/ecommerce/locales',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/channels/ecommerce',
            json: ECOMMERCE,
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourceSettings
                    source={{source: 'description', locale: null, scope: 'ecommerce'}}
                    attribute={{
                        label: 'Description',
                        code: 'description',
                        type: 'text',
                        scopable: true,
                        localizable: true,
                        attribute_group_code: '',
                        attribute_group_label: '',
                    }}
                    onChange={onChange}
                    errors={null}
                ></SourceSettings>
            </QueryClientProvider>
        </ThemeProvider>
    );

    openDropdown('source-parameter-locale-dropdown');

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();

    fireEvent.click(screen.getByText('French'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'description',
        locale: 'fr_FR',
        scope: 'ecommerce',
    });
});

test('it call onChange when the locale change with a non scopable attribute', async () => {
    const onChange = jest.fn();

    mockFetchResponses([
        {
            url: '/rest/catalogs/locales?page=1&limit=20',
            json: [EN, FR, DE],
        },
    ]);

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourceSettings
                    source={{source: 'description', locale: null, scope: null}}
                    attribute={{
                        label: 'Description',
                        code: 'description',
                        type: 'text',
                        scopable: false,
                        localizable: true,
                        attribute_group_code: '',
                        attribute_group_label: '',
                    }}
                    onChange={onChange}
                    errors={null}
                ></SourceSettings>
            </QueryClientProvider>
        </ThemeProvider>
    );

    openDropdown('source-parameter-locale-dropdown');

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();
    expect(await screen.findByText('German')).toBeInTheDocument();

    fireEvent.click(screen.getByText('French'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'description',
        locale: 'fr_FR',
        scope: null,
    });
});
