import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Operations} from './Operations';
import {AttributeTarget, DataMapping} from '../../models';
import {renderWithProviders} from 'feature/tests';

test('it does not display preview if no sample data is provided', async () => {
  const dataMapping: DataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: [],
  };

  await renderWithProviders(<Operations dataMapping={dataMapping} onRefreshSampleData={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).not.toBeInTheDocument();
});

test('it displays preview if sample data is provided', async () => {
  const dataMapping: DataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', 'product_3'],
  };

  await renderWithProviders(<Operations dataMapping={dataMapping} onRefreshSampleData={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).toBeInTheDocument();
  expect(screen.queryByText('product_1')).toBeInTheDocument();
  expect(screen.queryByText('product_2')).toBeInTheDocument();
  expect(screen.queryByText('product_3')).toBeInTheDocument();
});

test('it calls refresh sample data handler when user refreshes a not empty data', async () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping: DataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', 'product_3'],
  };

  await renderWithProviders(<Operations dataMapping={dataMapping} onRefreshSampleData={handleRefreshSampleData} />);

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  });

  expect(handleRefreshSampleData).toBeCalledWith(0);
});

test('it calls refresh sample data handler when user refresh an empty data', async () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping: DataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', null],
  };

  await renderWithProviders(<Operations dataMapping={dataMapping} onRefreshSampleData={handleRefreshSampleData} />);

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[2]);
  });

  expect(handleRefreshSampleData).toBeCalledWith(2);
});
