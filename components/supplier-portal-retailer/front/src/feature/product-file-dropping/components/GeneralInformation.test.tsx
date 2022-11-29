import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import 'jest-fetch-mock';
import {GeneralInformation} from './GeneralInformation';
import {ImportStatus} from '../models/ImportStatus';

test('it displays the general information on a product file.', () => {
    const productFile = {
        identifier: '037455b4-24a3-4404-a721-aca6f06d6293',
        originalFilename: 'file.xlsx',
        uploadedAt: '09/22/2022, 04:08 AM',
        contributor: 'jimmy@supplier.com',
        supplier: 'ffa51317-e609-481e-b6a3-63991b4e6dbe',
        supplierLabel: 'Los Pollos Hermanos',
        importStatus: ImportStatus.TO_IMPORT,
        importedAt: '09/23/2022, 04:08 AM',
        retailerComments: [],
        supplierComments: [],
    };
    renderWithProviders(<GeneralInformation productFile={productFile} />);

    expect(screen.getByText('jimmy@supplier.com')).toBeInTheDocument();
    expect(screen.getByText('Los Pollos Hermanos')).toBeInTheDocument();
    expect(screen.getByText('09/22/2022, 04:08 AM')).toBeInTheDocument();
    expect(screen.getByText('09/23/2022, 04:08 AM')).toBeInTheDocument();
});

test('it informs the user if the file has not been imported yet', () => {
    const productFile = {
        identifier: '037455b4-24a3-4404-a721-aca6f06d6293',
        originalFilename: 'file.xlsx',
        uploadedAt: '09/22/2022, 04:08 AM',
        contributor: 'jimmy@supplier.com',
        supplier: 'ffa51317-e609-481e-b6a3-63991b4e6dbe',
        supplierLabel: 'Los Pollos Hermanos',
        importStatus: ImportStatus.TO_IMPORT,
        importedAt: null,
        retailerComments: [],
        supplierComments: [],
    };
    renderWithProviders(<GeneralInformation productFile={productFile} />);

    expect(
        screen.getByText('supplier_portal.product_file_dropping.supplier_files.general_information.not_imported_yet')
    ).toBeInTheDocument();
});
