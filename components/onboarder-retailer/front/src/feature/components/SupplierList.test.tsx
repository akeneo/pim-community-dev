import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SupplierList} from './SupplierList';

test('it renders no result wording when there is no supplier matching the search', () => {
    renderWithProviders(
        <SupplierList
            suppliers={[]}
            onSearchChange={() => {}}
            searchValue={'unknown supplier'}
            totalSuppliers={0}
            onChangePage={() => {}}
            currentPage={0}
        />
    );

    expect(screen.getByText('onboarder.supplier.supplier_list.no_search_result')).toBeInTheDocument();
});

test('it informs when there is no supplier', () => {
    renderWithProviders(
        <SupplierList
            suppliers={[]}
            onSearchChange={() => {}}
            searchValue={''}
            totalSuppliers={0}
            onChangePage={() => {}}
            currentPage={0}
        />
    );

    expect(screen.getByText('onboarder.supplier.supplier_list.no_supplier')).toBeInTheDocument();
    expect(screen.getByText('onboarder.supplier.supplier_create.create_button.label')).toBeInTheDocument();
});

test('it renders the supplier list if there are some', () => {
    renderWithProviders(
        <SupplierList
            suppliers={[
                {code: 'foo', label: 'Foo', contributorsCount: 0},
                {code: 'bar', label: 'Bar', contributorsCount: 0},
            ]}
            onSearchChange={() => {}}
            searchValue={''}
            totalSuppliers={2}
            onChangePage={() => {}}
            currentPage={0}
        />
    );

    expect(screen.getByTestId('foo')).toBeInTheDocument();
    expect(screen.getByTestId('bar')).toBeInTheDocument();
});
