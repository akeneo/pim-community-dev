import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Operations} from './Operations';
import {AttributeTarget, DataMapping} from '../../models';
import {renderWithProviders} from 'feature/tests';

const dataMapping: DataMapping = {
  uuid: '1ff0d8da-2438-4f2a-ae29-2ea513100924',
  target: {} as AttributeTarget,
  sources: ['8cb22256-3bc7-494e-9b24-33a1c52fe758'],
  operations: [],
  sample_data: ['product_1', 'product_2', 'product_3'],
};

jest.mock('../../hooks/usePreviewData', () => ({
  usePreviewData: () => [false, ['product_1', 'product_2', 'product_3'], false],
}));

test('it displays preview if sample data is provided', async () => {
  await renderWithProviders(
    <Operations
      dataMapping={dataMapping}
      compatibleOperations={[]}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.input_title')).toBeInTheDocument();
  expect(screen.queryByText('product_1')).toBeInTheDocument();
  expect(screen.queryByText('product_2')).toBeInTheDocument();
  expect(screen.queryByText('product_3')).toBeInTheDocument();
});

test('it does not display preview if no source is set', async () => {
  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        sources: [],
      }}
      compatibleOperations={[]}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.no_source')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.input_title')).not.toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.output_title')).not.toBeInTheDocument();
});

test('it calls refresh sample data handler when user refreshes a data', async () => {
  const handleRefreshSampleData = jest.fn();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        sample_data: ['product_1', 'product_2', null],
      }}
      compatibleOperations={[]}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={handleRefreshSampleData}
      validationErrors={[]}
    />
  );

  await act(async () => {
    userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[2]);
  });

  expect(handleRefreshSampleData).toBeCalledWith(2);
});

test('it displays compatible operation when present in data mapping', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [
          {uuid: expect.any(String), modes: ['remove'], type: 'clean_html'},
          // @ts-expect-error unknown operation
          {type: 'unknown_operation'},
        ],
      }}
      compatibleOperations={['clean_html']}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.title')).toBeInTheDocument();
  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.operations.unknown_operation')
  ).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it can add a compatible operation in data mapping', async () => {
  const handleOperationsChange = jest.fn();

  await renderWithProviders(
    <Operations
      dataMapping={dataMapping}
      compatibleOperations={['clean_html']}
      onOperationsChange={handleOperationsChange}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.add'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html.title'));

  expect(handleOperationsChange).toHaveBeenCalledWith([
    {uuid: expect.any(String), modes: ['remove', 'decode'], type: 'clean_html'},
  ]);
});

test('it can remove an operation from data mapping', async () => {
  const handleOperationsChange = jest.fn();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}],
      }}
      compatibleOperations={['clean_html']}
      onOperationsChange={handleOperationsChange}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));
  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleOperationsChange).toHaveBeenCalledWith([]);
});

test('it tells when there are no more available operations and hides the add button', async () => {
  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}],
      }}
      compatibleOperations={['clean_html']}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.no_available.text')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.operations.add')).not.toBeInTheDocument();
});

test('it tells when the operation block is not found', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [
          // @ts-expect-error unknown operation
          {type: 'unknown_operation'},
        ],
      }}
      // @ts-expect-error unknown operation
      compatibleOperations={['unknown_operation']}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.operations.unknown_operation')
  ).not.toBeInTheDocument();

  expect(mockedConsole).toHaveBeenCalledWith('No operation block found for operation type "unknown_operation"');
  mockedConsole.mockRestore();
});

test('it can handle an operation change', async () => {
  const handleOperationsChange = jest.fn();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [{uuid: expect.any(String), type: 'split', separator: ','}],
      }}
      compatibleOperations={['split']}
      onOperationsChange={handleOperationsChange}
      onRefreshSampleData={jest.fn()}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('akeneo.tailored_import.data_mapping.operations.common.collapse'));
  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByTitle('semicolon'));

  expect(handleOperationsChange).toHaveBeenCalledWith([{uuid: expect.any(String), type: 'split', separator: ';'}]);
});
