import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CreateSupplier} from "./CreateSupplier";
import userEvent from '@testing-library/user-event';

test('it renders only the button by default', () => {
    renderWithProviders(
        <CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'}/>
    );
    expect(screen.getByText('create')).toBeInTheDocument();
    assertModalIsClosed();
});

test('it renders the modal when clicking on the create button', () => {
    renderWithProviders(
        <CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'}/>
    );
    openModal();
    assertModalIsOpen()
});

test('it can save a supplier', async () => {
    global.fetch = jest.fn();

    const onSupplierCreated = jest.fn();

    renderWithProviders(
        <CreateSupplier onSupplierCreated={onSupplierCreated} createButtonlabel={'create'}/>
    );

    openModal();
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.create_supplier.code.label'), 'supplier1');
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.create_supplier.label.label'), 'Supplier 1');

    await act(async () => {
        userEvent.click(screen.getByText('pim_common.save'));
    });

    expect(onSupplierCreated).toHaveBeenCalledTimes(1);
    assertModalIsClosed();
});

function openModal() {
    userEvent.click(screen.getByText('create'));
}
function assertModalIsOpen() {
    expect(screen.queryByRole('dialog')).toBeInTheDocument();
}
function assertModalIsClosed() {
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
}
