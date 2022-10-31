import React from 'react';
import {act, screen} from '@testing-library/react';
import {mockedDependencies, NotificationLevel, renderWithProviders} from '@akeneo-pim-community/shared';
import userEvent from '@testing-library/user-event';
import {ProductImportProfilesModal} from './ProductImportProfilesModal';

test('it renders only a button to open the modal', () => {
    renderWithProviders(<ProductImportProfilesModal />);
    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label')
    ).toBeInTheDocument();
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it renders the modal with a list of product file imports when clicking on the import file button', async () => {
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: true,
        json: async () => [
            {code: 'import1', label: 'Import 1'},
            {code: 'import2', label: 'Import 2'},
        ],
    }));

    await act(async () => {
        renderWithProviders(<ProductImportProfilesModal />);
        openModal();
    });

    expect(screen.queryByRole('dialog')).toBeInTheDocument();
    expect(screen.getByText('pim_common.cancel')).toBeInTheDocument();

    const submitButton = screen.getByText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_button_label'
    );
    expect(submitButton).toBeDisabled();

    const selectField = screen.getByLabelText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_field_label'
    );
    userEvent.click(selectField);
    expect(screen.getByText('Import 1')).toBeInTheDocument();
    expect(screen.getByText('Import 2')).toBeInTheDocument();
    userEvent.click(screen.getByText('Import 1'));
    expect(submitButton).toBeEnabled();
});

test('it warns the user if the product import profiles list cannot be retrieved', async () => {
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await act(async () => {
        renderWithProviders(<ProductImportProfilesModal />);
        openModal();
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.title',
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.content'
    );
});

function openModal() {
    userEvent.click(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label')
    );
}
