import {renderWithProviders} from '../../../tests';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {OperationSampleData} from './OperationSampleData';

test('it displays the sample data', async () => {
  const handleRefreshSampleData = jest.fn();

  await renderWithProviders(
    <OperationSampleData
      loadingSampleData={[]}
      sampleData={['product_1', 'product_2', null]}
      onRefreshSampleData={handleRefreshSampleData}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.input_title')).toBeInTheDocument();
  expect(screen.getByText('product_1')).toBeInTheDocument();
  expect(screen.getByText('product_2')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.preview.placeholder')).toBeInTheDocument();
});

test('it calls refresh sample data handler when user refreshes a non empty data', async () => {
  const handleRefreshSampleData = jest.fn();

  await renderWithProviders(
    <OperationSampleData
      loadingSampleData={[]}
      sampleData={['product_1', 'product_2', null]}
      onRefreshSampleData={handleRefreshSampleData}
    />
  );

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  });

  expect(handleRefreshSampleData).toBeCalledWith(0);
});

test('it calls refresh sample data handler when user refreshes an empty data', async () => {
  const handleRefreshSampleData = jest.fn();

  await renderWithProviders(
    <OperationSampleData
      loadingSampleData={[]}
      sampleData={['product_1', 'product_2', null]}
      onRefreshSampleData={handleRefreshSampleData}
    />
  );

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[2]);
  });

  expect(handleRefreshSampleData).toBeCalledWith(2);
});

test('it cannot refresh sample data handler when data is currently loading', async () => {
  const handleRefreshSampleData = jest.fn();

  await renderWithProviders(
    <OperationSampleData
      loadingSampleData={[0]}
      sampleData={['product_1', 'product_2', null]}
      onRefreshSampleData={handleRefreshSampleData}
    />
  );

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  });

  expect(handleRefreshSampleData).not.toBeCalled();
});
