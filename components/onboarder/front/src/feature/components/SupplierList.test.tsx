import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SupplierList} from "./SupplierList";

test('it renders only the no data component when there is no supplier matching the search', () => {
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

test('it renders only the empty component when there is no supplier', () => {
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

    expect(screen.getByText('onboarder.supplier.supplier_create.create_button.label')).toBeInTheDocument();
});

test('it renders the supplier list if there are some', () => {
    renderWithProviders(
        <SupplierList
            suppliers={[{code: 'foo', label: 'Foo', contributorsCount: 0}]}
            onSearchChange={() => {}}
            searchValue={''}
            totalSuppliers={1}
            onChangePage={() => {}}
            currentPage={0}
        />
    );

    expect(screen.getByText('onboarder.supplier.supplier_list.columns.supplier')).toBeInTheDocument();
    expect(screen.getByText('Foo')).toBeInTheDocument();
})
