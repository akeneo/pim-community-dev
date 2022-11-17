import React from 'react';
import {act, screen} from '@testing-library/react';
import {mockedDependencies, NotificationLevel, renderWithProviders} from '@akeneo-pim-community/shared';
import userEvent from '@testing-library/user-event';
import {ProductFileImportConfigurationsModal} from './ProductFileImportConfigurationsModal';

test('it renders only a button to open the modal', () => {
    renderWithProviders(<ProductFileImportConfigurationsModal />);
    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label')
    ).toBeInTheDocument();
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it renders the modal with a list of product file import configurations when clicking on the import file button', async () => {
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: true,
        json: async () => [
            {code: 'import1', label: 'Import 1'},
            {code: 'import2', label: 'Import 2'},
        ],
    }));

    await act(async () => {
        renderWithProviders(<ProductFileImportConfigurationsModal />);
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

test('it warns the user if the product file import configurations list cannot be retrieved', async () => {
    global.fetch = jest.fn().mockImplementationOnce(async () => ({
        ok: false,
    }));
    const notify = jest.spyOn(mockedDependencies, 'notify');

    await act(async () => {
        renderWithProviders(<ProductFileImportConfigurationsModal />);
        openModal();
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.title',
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_loading_product_import_profiles.content'
    );
});

test('it allows a user to launch an import of a product file', async () => {
    const processTrackerUrl = '#/job/show/28';
    global.fetch = jest
        .fn()
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => [
                {code: 'import1', label: 'Import 1'},
                {code: 'import2', label: 'Import 2'},
            ],
        }))
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => processTrackerUrl,
        }));

    await act(async () => {
        renderWithProviders(
            <ProductFileImportConfigurationsModal productFileIdentifier="66ac030b-073d-4966-9624-5d992ac3a8c3" />
        );
        openModal();
    });

    const selectField = screen.getByLabelText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_field_label'
    );
    userEvent.click(selectField);
    userEvent.click(screen.getByText('Import 1'));

    const submitButton = screen.getByText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_button_label'
    );
    await act(async () => {
        userEvent.click(submitButton);
    });
    expect(window.location.href).toContain(processTrackerUrl);
});

test('it failed to launch an import of a product file', async () => {
    const notify = jest.spyOn(mockedDependencies, 'notify');
    global.fetch = jest
        .fn()
        .mockImplementationOnce(async () => ({
            ok: true,
            json: async () => [
                {code: 'import1', label: 'Import 1'},
                {code: 'import2', label: 'Import 2'},
            ],
        }))
        .mockImplementationOnce(async () => ({
            ok: false,
            status: 500,
        }));

    await act(async () => {
        renderWithProviders(
            <ProductFileImportConfigurationsModal productFileIdentifier="66ac030b-073d-4966-9624-5d992ac3a8c3" />
        );
        openModal();
    });

    const selectField = screen.getByLabelText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_field_label'
    );
    userEvent.click(selectField);
    userEvent.click(screen.getByText('Import 1'));

    const submitButton = screen.getByText(
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.import_button_label'
    );
    await act(async () => {
        userEvent.click(submitButton);
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_launching_product_import.unknown_error.title',
        'supplier_portal.product_file_dropping.supplier_files.product_files_modal.error_launching_product_import.unknown_error.content'
    );
});

function openModal() {
    userEvent.click(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.discussion.import_file_button_label')
    );
}
