import React from 'react';
import {act, screen} from '@testing-library/react';
import {mockedDependencies, renderWithProviders, NotificationLevel} from '@akeneo-pim-community/shared';
import {DeleteSupplier} from './DeleteSupplier';
import userEvent from '@testing-library/user-event';

test('it renders only the button by default', () => {
    renderWithProviders(<DeleteSupplier onSupplierDeleted={() => {}} identifier={''} />);
    expect(screen.getByTitle('pim_common.delete')).toBeInTheDocument();
    assertModalIsClosed();
});

test('it renders the modal when clicking on the delete button', () => {
    renderWithProviders(<DeleteSupplier onSupplierDeleted={() => {}} identifier={''} />);
    openModal();
    assertModalIsOpen();
});

test('it can delete a supplier', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        status: 200,
    }));
    const onSupplierDeleted = jest.fn();
    const notify = jest.spyOn(mockedDependencies, 'notify');

    renderWithProviders(<DeleteSupplier onSupplierDeleted={onSupplierDeleted} identifier={''} />);
    openModal();

    await act(async () => {
        userEvent.click(screen.getByTestId('delete-button'));
    });

    expect(onSupplierDeleted).toHaveBeenCalledTimes(1);
    assertModalIsClosed();
    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.SUCCESS,
        'onboarder.supplier.supplier_delete.sucess_message'
    );
});

function openModal() {
    userEvent.click(screen.getByTitle('pim_common.delete'));
}
function assertModalIsOpen() {
    expect(screen.queryByRole('dialog')).toBeInTheDocument();
}
function assertModalIsClosed() {
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
}
