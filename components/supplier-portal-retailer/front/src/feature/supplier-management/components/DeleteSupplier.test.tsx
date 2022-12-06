import React from 'react';
import {act, screen} from '@testing-library/react';
import {mockedDependencies, renderWithProviders, NotificationLevel} from '@akeneo-pim-community/shared';
import {DeleteSupplier} from './DeleteSupplier';
import userEvent from '@testing-library/user-event';

test('it can delete a supplier', async () => {
    // @ts-ignore
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        status: 200,
    }));
    const onSupplierDeleted = jest.fn();
    const notify = jest.spyOn(mockedDependencies, 'notify');

    renderWithProviders(
        <DeleteSupplier onSupplierDeleted={onSupplierDeleted} identifier={''} onCloseModal={() => {}} />
    );

    await act(async () => {
        userEvent.click(screen.getByTestId('delete-button'));
    });

    expect(onSupplierDeleted).toHaveBeenCalledTimes(1);
    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.SUCCESS,
        'supplier_portal.supplier.supplier_delete.sucess_message'
    );
});
