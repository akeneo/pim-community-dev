import React from 'react';
import {screen} from '@testing-library/react';
import {OperationPreviewData} from './OperationPreviewData';
import {renderWithProviders} from '../../../tests';

test('it displays the preview data', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      previewData={[
        {type: 'string', value: 'product_1'},
        {type: 'string', value: 'product_2'},
        {type: 'null', value: null},
      ]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('product_1')).toBeInTheDocument();
  expect(screen.getByText('product_2')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.placeholder')).toBeInTheDocument();
});

test('it displays an error when preview cannot be generated', async () => {
  await renderWithProviders(
    <OperationPreviewData isLoading={false} previewData={[]} isOpen={false} hasErrors={true} />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')
  ).toBeInTheDocument();
  expect(screen.queryByText('product_1')).not.toBeInTheDocument();
  expect(screen.queryByText('product_2')).not.toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.placeholder')).not.toBeInTheDocument();
});

test('it displays a tag element if preview data is an array of arrays', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      previewData={[
        {type: 'array', value: ['product_1', 'product_11']},
        {type: 'string', value: 'product_2'},
        {type: 'null', value: null},
      ]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('product_1')).toBeInTheDocument();
  expect(screen.getByText('product_11')).toBeInTheDocument();
  expect(screen.getByText('product_2')).toBeInTheDocument();
});

test('it displays preview data for measurement values', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      previewData={[{type: 'measurement', unit: 'GRAM', value: '12'}]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('12 GRAM')).toBeInTheDocument();
});

test('it displays preview data for boolean values', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      previewData={[
        {type: 'boolean', value: true},
        {type: 'boolean', value: false},
      ]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(screen.getByText('true')).toBeInTheDocument();
  expect(screen.getByText('false')).toBeInTheDocument();
});

test('it displays a placeholder when having invalid values', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      previewData={[{type: 'invalid', error_key: 'invalid.value'}]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')
  ).toBeInTheDocument();
});

test('it displays a placeholder when having unknown values', async () => {
  await renderWithProviders(
    <OperationPreviewData
      isLoading={false}
      // @ts-expect-error unknown value type
      previewData={[{type: 'unknown'}]}
      isOpen={true}
      hasErrors={false}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.output_title')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.preview.unable_to_generate_preview_data')
  ).toBeInTheDocument();
});

test('it displays nothing when preview data is undefined', async () => {
  await renderWithProviders(
    <OperationPreviewData isLoading={false} previewData={undefined} isOpen={true} hasErrors={false} />
  );

  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.output_title')).not.toBeInTheDocument();
});
