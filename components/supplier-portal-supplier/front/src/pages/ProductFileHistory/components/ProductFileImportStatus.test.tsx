import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../tests';
import {ProductFileImportStatus} from './ProductFileImportStatus';
import {ImportStatus} from '../model/ImportStatus';

test('it renders a product file import status approved without comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.COMPLETED} hasComments={false} />);

    expect(screen.getByText('approved')).toBeInTheDocument();
});

test('it renders a product file import status approved with comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.COMPLETED} hasComments={true} />);

    expect(screen.getByText('approved')).toBeInTheDocument();
});

test('it renders a product file import status failed with comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.FAILED} hasComments={true} />);

    expect(screen.getByText('commented')).toBeInTheDocument();
});

test('it renders a product file import status failed without comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.FAILED} hasComments={false} />);

    expect(screen.getByText('submitted')).toBeInTheDocument();
});

test('it renders a product file import status to import with comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.TO_IMPORT} hasComments={true} />);

    expect(screen.getByText('commented')).toBeInTheDocument();
});

test('it renders a product file import status to import without comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.TO_IMPORT} hasComments={false} />);

    expect(screen.getByText('submitted')).toBeInTheDocument();
});

test('it renders a product file import status in progress with comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.IN_PROGRESS} hasComments={true} />);

    expect(screen.getByText('commented')).toBeInTheDocument();
});

test('it renders a product file import status in progress without comments', () => {
    renderWithProviders(<ProductFileImportStatus importStatus={ImportStatus.IN_PROGRESS} hasComments={false} />);

    expect(screen.getByText('submitted')).toBeInTheDocument();
});
