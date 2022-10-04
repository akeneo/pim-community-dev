import React from 'react';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {FilterChannel} from './FilterChannel';

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

const ECOMMERCE = {code: 'ecommerce', label: 'E-commerce'};
const PRINT = {code: 'print', label: 'Print'};

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/channels?page=1&limit=20',
            json: [ECOMMERCE, PRINT],
        },
        {
            url: '/rest/catalogs/channels?codes=print',
            json: [PRINT],
        },
        {
            url: '/rest/catalogs/channels?codes=',
            json: [],
        },
    ]);
});

test('it sets a product value filter on the channel', async () => {
    const onChange = jest.fn();
    const productValueFilters = {};

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterChannel productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    openDropdown('product-value-filter-by-channel');

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('E-commerce'));

    expect(onChange).toHaveBeenCalledWith({channels: ['ecommerce']});
});

test('it adds a product value filter on the channel', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterChannel productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Print')).toBeInTheDocument();
    expect(screen.queryByText('E-commerce')).not.toBeInTheDocument();

    openDropdown('product-value-filter-by-channel');

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('E-commerce'));

    expect(onChange).toHaveBeenCalledWith({
        channels: ['print', 'ecommerce'],
        locales: ['en_US', 'fr_FR'],
    });
});

test('it removes a product value filter on the channel', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterChannel productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('Print')).toBeInTheDocument();
    expect(screen.queryByText('E-commerce')).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('akeneo_catalogs.product_value_filters.action.remove'));

    expect(onChange).toHaveBeenCalledWith({
        channels: [],
        locales: ['en_US', 'fr_FR'],
    });
});
