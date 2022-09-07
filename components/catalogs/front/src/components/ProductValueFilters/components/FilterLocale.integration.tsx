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
            url: '/rest/catalogs/locales',
            json: [EN, FR, DE],
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

    expect(await screen.findByText('E-commerce')).toBeInTheDocument();
    expect(await screen.findByText('Print')).toBeInTheDocument();

    fireEvent.click(await screen.findByText('E-commerce'));

    expect(onChange).toHaveBeenCalledWith({channel: ['ecommerce']});
});
//
// test('it adds a product value filter on the channel', async () => {
//     const onChange = jest.fn();
//     const productValueFilters = {
//         channel: ['print'],
//         locale: ['en_US', 'fr_FR'],
//     };
//
//     render(
//         <ThemeProvider theme={pimTheme}>
//             <ReactQueryWrapper>
//                 <FilterChannel productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
//             </ReactQueryWrapper>
//         </ThemeProvider>
//     );
//
//     expect(await screen.findByText('Print')).toBeInTheDocument();
//     expect(screen.queryByText('E-commerce')).not.toBeInTheDocument();
//
//     openDropdown('product-value-filter-by-locale');
//
//     expect(await screen.findByText('E-commerce')).toBeInTheDocument();
//
//     fireEvent.click(await screen.findByText('E-commerce'));
//
//     expect(onChange).toHaveBeenCalledWith({
//         channel: ['print', 'ecommerce'],
//         locale: ['en_US', 'fr_FR'],
//     });
// });
//
// test('it removes a product value filter on the channel', async () => {
//     const onChange = jest.fn();
//     const productValueFilters = {
//         channel: ['print'],
//         locale: ['en_US', 'fr_FR'],
//     };
//
//     render(
//         <ThemeProvider theme={pimTheme}>
//             <ReactQueryWrapper>
//                 <FilterChannel productValueFilters={productValueFilters} onChange={onChange} isInvalid={false} />
//             </ReactQueryWrapper>
//         </ThemeProvider>
//     );
//
//     expect(await screen.findByText('Print')).toBeInTheDocument();
//     expect(screen.queryByText('E-commerce')).not.toBeInTheDocument();
//
//     fireEvent.click(screen.getByTitle('akeneo_catalogs.product_value_filters.action.remove'));
//
//     expect(onChange).toHaveBeenCalledWith({
//         channel: [],
//         locale: ['en_US', 'fr_FR'],
//     });
// });
