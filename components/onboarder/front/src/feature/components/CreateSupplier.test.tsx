import React from 'react';
import {act, screen} from '@testing-library/react';
import {mockedDependencies, renderWithProviders, NotificationLevel} from '@akeneo-pim-community/shared';
import {CreateSupplier} from './CreateSupplier';
import userEvent from '@testing-library/user-event';

test('it renders only the button by default', () => {
    renderWithProviders(<CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'} />);
    expect(screen.getByText('create')).toBeInTheDocument();
    assertModalIsClosed();
});

test('it renders the modal when clicking on the create button', () => {
    renderWithProviders(<CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'} />);
    openModal();
    assertModalIsOpen();
});

test('it can save a supplier', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: true,
        json: async () => {},
    }));

    const onSupplierCreated = jest.fn();

    renderWithProviders(<CreateSupplier onSupplierCreated={onSupplierCreated} createButtonlabel={'create'} />);

    openModal();
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.code.label'), 'supplier1');
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.label.label'), 'Supplier 1');

    await act(async () => {
        userEvent.click(screen.getByText('pim_common.save'));
    });

    expect(onSupplierCreated).toHaveBeenCalledTimes(1);
    assertModalIsClosed();
});

test('it renders an error notification when a supplier with the same code already exists', async () => {
    global.fetch = jest.fn().mockImplementation(async () => ({
        ok: false,
        status: 409,
    }));

    const onSupplierCreated = jest.fn();
    const notify = jest.spyOn(mockedDependencies, 'notify');

    renderWithProviders(<CreateSupplier onSupplierCreated={onSupplierCreated} createButtonlabel={'create'} />);

    openModal();
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.code.label'), 'supplier1');
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.label.label'), 'Supplier 1');

    await act(async () => {
        userEvent.click(screen.getByText('pim_common.save'));
    });

    expect(onSupplierCreated).not.toHaveBeenCalled();
    assertModalIsOpen();
    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'onboarder.supplier.supplier_create.error.supplier_already_exists'
    );
});

test('The supplier code can be generated from the supplier label', () => {
    renderWithProviders(<CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'} />);

    openModal();
    userEvent.type(
        screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.label.label'),
        '  Supplier #1     '
    );

    expect(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.code.label')).toHaveValue(
        'supplier__1'
    );
});

test('The supplier code is not generated anymore after typing a label', () => {
    renderWithProviders(<CreateSupplier onSupplierCreated={() => {}} createButtonlabel={'create'} />);

    openModal();

    const codeField = screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.code.label');
    const labelField = screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.label.label');

    userEvent.type(labelField, 'Supplier 1');
    //type() appends instead of replacing. See https://testing-library.com/docs/ecosystem-user-event/#typeelement-text-options
    userEvent.clear(codeField);
    userEvent.type(codeField, 'supplier1');
    userEvent.clear(labelField);
    userEvent.type(labelField, 'Supplier number 1');

    expect(codeField).toHaveValue('supplier1');
});

test('Backend is not called when code or label is empty', async () => {
    const onSupplierCreated = jest.fn();

    renderWithProviders(<CreateSupplier onSupplierCreated={onSupplierCreated} createButtonlabel={'create'} />);

    openModal();
    userEvent.type(screen.getByPlaceholderText('onboarder.supplier.supplier_create.modal.code.label'), 'supplier1');

    await act(async () => {
        userEvent.click(screen.getByText('pim_common.save'));
    });

    expect(onSupplierCreated).not.toHaveBeenCalled();
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
