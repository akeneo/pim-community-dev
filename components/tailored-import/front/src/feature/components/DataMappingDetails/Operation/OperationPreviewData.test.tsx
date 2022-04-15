import React from 'react';
import {screen} from '@testing-library/react';
import {OperationPreviewData} from './OperationPreviewData';
import {renderWithProviders} from '../../../tests';

test('it display the preview data', async () => {
  await renderWithProviders(
    <OperationPreviewData isLoading={false} previewData={['product_1', 'product_2', null]} hasErrors={false} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('product_1')).toBeInTheDocument();
  expect(screen.getByText('product_2')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.placeholder')).toBeInTheDocument();
});

test('it display an error when preview cannot be generated', async () => {
  await renderWithProviders(<OperationPreviewData isLoading={false} previewData={[]} hasErrors={true} />);

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')
  ).toBeInTheDocument();
  expect(screen.queryByText('product_1')).not.toBeInTheDocument();
  expect(screen.queryByText('product_2')).not.toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.placeholder')).not.toBeInTheDocument();
});
