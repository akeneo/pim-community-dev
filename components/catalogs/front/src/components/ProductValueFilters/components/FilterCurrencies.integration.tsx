import React from 'react';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {FilterCurrencies} from './FilterCurrencies';

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/currencies',
            json: ['EUR', 'USD'],
        },
    ]);
});

test('it sets a product value filter on the currencies', async () => {
    const onChange = jest.fn();
    const productValueFilters = {};

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterCurrencies productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    openDropdown('product-value-filter-by-currency');

    expect(await screen.findByText('EUR')).toBeInTheDocument();
    expect(await screen.findByText('USD')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('USD'));

    expect(onChange).toHaveBeenCalledWith({currencies: ['USD']});
});

test('it adds a product value filter on the currencies', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
        currencies: ['EUR'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterCurrencies productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('EUR')).toBeInTheDocument();
    expect(screen.queryByText('USD')).not.toBeInTheDocument();

    openDropdown('product-value-filter-by-currency');

    expect(await screen.findByText('USD')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('USD'));

    expect(onChange).toHaveBeenCalledWith({
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
        currencies: ['EUR', 'USD'],
    });
});

test('it removes a product value filter on the currencies', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
        currencies: ['EUR'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterCurrencies productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('EUR')).toBeInTheDocument();
    expect(screen.queryByText('USD')).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('akeneo_catalogs.product_value_filters.action.remove'));

    expect(onChange).toHaveBeenCalledWith({
        channels: ['print'],
        locales: ['en_US', 'fr_FR'],
        currencies: [],
    });
});
