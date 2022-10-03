import React from 'react';
import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {fireEvent, render, screen, within} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {FilterLocale} from './FilterLocale';

const openDropdown = (selector: string): void => {
    const container = screen.getByTestId(selector);
    const input = within(container).getByRole('textbox');
    fireEvent.click(input);
    fireEvent.focus(input);
};

const EN = {code: 'en_US', label: 'English'};
const FR = {code: 'fr_FR', label: 'French'};
const DE = {code: 'de_DE', label: 'German'};

beforeEach(() => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/locales?page=1&limit=20',
            json: [EN, FR, DE],
        },
        {
            url: '/rest/catalogs/locales?codes=de_DE',
            json: [DE],
        },
        {
            url: '/rest/catalogs/locales?codes=en_US',
            json: [EN],
        },
        {
            url: '/rest/catalogs/locales?codes=',
            json: [],
        },
    ]);
});

test('it sets a product value filter on the locale', async () => {
    const onChange = jest.fn();
    const productValueFilters = {};

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterLocale productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    openDropdown('product-value-filter-by-locale');

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(await screen.findByText('French')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('English'));

    expect(onChange).toHaveBeenCalledWith({locales: ['en_US']});
});

test('it adds a product value filter on the locale', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print', 'ecommerce'],
        locales: ['de_DE'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterLocale productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('German')).toBeInTheDocument();
    expect(screen.queryByText('English')).not.toBeInTheDocument();

    openDropdown('product-value-filter-by-locale');

    expect(await screen.findByText('English')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('English'));

    expect(onChange).toHaveBeenCalledWith({
        channels: ['print', 'ecommerce'],
        locales: ['de_DE', 'en_US'],
    });
});

test('it removes a product value filter on the locale', async () => {
    const onChange = jest.fn();
    const productValueFilters = {
        channels: ['print'],
        locales: ['en_US'],
    };

    render(
        <ThemeProvider theme={pimTheme}>
            <ReactQueryWrapper>
                <FilterLocale productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
            </ReactQueryWrapper>
        </ThemeProvider>
    );

    expect(await screen.findByText('English')).toBeInTheDocument();
    expect(screen.queryByText('French')).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('akeneo_catalogs.product_value_filters.action.remove'));

    expect(onChange).toHaveBeenCalledWith({
        channels: ['print'],
        locales: [],
    });
});
