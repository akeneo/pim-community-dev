import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {SourceSelection} from './SourceSelection';
import {QueryClient, QueryClientProvider} from 'react-query';
import {mockFetchResponses} from '../../../../../tests/mockFetchResponses';

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes-by-target-type-and-target-format?page=1&limit=20&search=&targetType=string&targetFormat=',
            json: [
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

test('it initialise default parameter for string type', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourceSelection
                    target={{code: 'erp_name', label: 'ERP name', type: 'string', format: null}}
                    source={null}
                    onChange={onChange}
                    errors={null}
                ></SourceSelection>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));
    fireEvent.click(await screen.findByText('EAN'));

    expect(onChange).toHaveBeenCalledWith({
        source: 'ean',
        locale: null,
        scope: null,
        parameters: {
            default: null,
        },
    });
});

test('it does not initialise default parameter for number type', async () => {
    const onChange = jest.fn();

    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourceSelection
                    target={{code: 'weight', label: 'Weight', type: 'number', format: null}}
                    source={null}
                    onChange={onChange}
                    errors={null}
                ></SourceSelection>
            </QueryClientProvider>
        </ThemeProvider>
    );

    fireEvent.mouseDown(await screen.findByTestId('product-mapping-select-attribute'));
    fireEvent.click(await screen.findByText('Weight'));

    expect(onChange).not.toHaveBeenCalledWith({
        source: 'ean',
        locale: null,
        scope: null,
        parameters: {
            default: null,
        },
    });
});
