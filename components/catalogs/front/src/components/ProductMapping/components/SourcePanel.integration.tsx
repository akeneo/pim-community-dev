import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {QueryClient, QueryClientProvider} from 'react-query';
import {SourcePanel} from './SourcePanel';
import {Source} from '../models/Source';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';

test('it displays a placeholder if there is no target selected', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target={null}
                    source={{source: null, scope: null, locale: null}}
                    onChange={jest.fn()}
                    errors={null}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('akeneo_catalogs.product_mapping.source.placeholder.title')).toBeInTheDocument();
});

test('it displays the target as a title', () => {
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target={{code: 'erp_name', label: 'ERP name', type: 'string', format: null}}
                    source={{source: null, scope: null, locale: null}}
                    onChange={jest.fn()}
                    errors={null}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(screen.getByText('ERP name')).toBeInTheDocument();
});

test('it displays a message when the selected source has no parameters', () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/weight',
            json: {
                code: 'weight',
                label: 'Wame',
                type: 'pim_catalog_number',
                scopable: false,
                localizable: false,
            },
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
    const source: Source = {
        source: 'weight',
        locale: null,
        scope: null,
    };
    render(
        <ThemeProvider theme={pimTheme}>
            <QueryClientProvider client={new QueryClient()}>
                <SourcePanel
                    target={{code: 'weight', label: 'Weight', type: 'number', format: null}}
                    source={source}
                    onChange={jest.fn()}
                    errors={null}
                ></SourcePanel>
            </QueryClientProvider>
        </ThemeProvider>
    );

    expect(
        screen.getByText('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')
    ).toBeInTheDocument();
});
