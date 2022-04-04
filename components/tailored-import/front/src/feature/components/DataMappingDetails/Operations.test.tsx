import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Operations} from './Operations';
import {AttributeTarget, DataMapping} from '../../models';
import userEvent from '@testing-library/user-event';

test("it don't display preview if no sample data is provided", () => {
  const dataMapping: DataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: [],
  };

  renderWithProviders(<Operations dataMapping={dataMapping} loadingSampleData={[]} onRefreshSampleData={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).not.toBeInTheDocument();
});

test('it display preview if sample data is provided', () => {
  const dataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', 'product_3'],
  } as DataMapping;

  renderWithProviders(<Operations dataMapping={dataMapping} loadingSampleData={[]} onRefreshSampleData={jest.fn()} />);

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).toBeInTheDocument();
  expect(screen.queryByText('product_1')).toBeInTheDocument();
  expect(screen.queryByText('product_2')).toBeInTheDocument();
  expect(screen.queryByText('product_3')).toBeInTheDocument();
});

test('it call refresh sample data handler when user refresh a not empty data', () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', 'product_3'],
  } as DataMapping;

  renderWithProviders(
    <Operations dataMapping={dataMapping} loadingSampleData={[]} onRefreshSampleData={handleRefreshSampleData} />
  );

  userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  expect(handleRefreshSampleData).toBeCalledWith(0);
});

test('it call refresh sample data handler when user refresh an empty data', () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', null],
  } as DataMapping;

  renderWithProviders(
    <Operations dataMapping={dataMapping} loadingSampleData={[]} onRefreshSampleData={handleRefreshSampleData} />
  );

  userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[2]);
  expect(handleRefreshSampleData).toBeCalledWith(2);
});

test('it cannot refresh sample data while non empty data is reloading', () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', 'product_3'],
  } as DataMapping;

  renderWithProviders(
    <Operations dataMapping={dataMapping} loadingSampleData={[0]} onRefreshSampleData={handleRefreshSampleData} />
  );

  userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  expect(handleRefreshSampleData).not.toBeCalled();
});

test('it cannot refresh sample data while empty data is reloading', () => {
  const handleRefreshSampleData = jest.fn();
  const dataMapping = {
    uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
    target: {} as AttributeTarget,
    sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
    operations: [],
    sample_data: ['product_1', 'product_2', null],
  } as DataMapping;

  renderWithProviders(
    <Operations dataMapping={dataMapping} loadingSampleData={[2]} onRefreshSampleData={handleRefreshSampleData} />
  );

  userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[2]);
  expect(handleRefreshSampleData).not.toBeCalled();
});
