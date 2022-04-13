import React from 'react';
import {screen, act, within} from '@testing-library/react';
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
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).toBeInTheDocument();
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
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.no_source')).toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).not.toBeInTheDocument();
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
          {type: 'clean_html_tags'},
          // @ts-expect-error unknown operation
          {type: 'unknown_operation'},
        ],
      }}
      compatibleOperations={['clean_html_tags']}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags')).toBeInTheDocument();
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
      compatibleOperations={['clean_html_tags']}
      onOperationsChange={handleOperationsChange}
      onRefreshSampleData={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.add'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags'));

  expect(handleOperationsChange).toHaveBeenCalledWith([{type: 'clean_html_tags'}]);
});

test('it can remove an operation from data mapping', async () => {
  const handleOperationsChange = jest.fn();

  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [{type: 'clean_html_tags'}],
      }}
      compatibleOperations={['clean_html_tags']}
      onOperationsChange={handleOperationsChange}
      onRefreshSampleData={jest.fn()}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));
  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleOperationsChange).toHaveBeenCalledWith([]);
});

test('it cannot add an operation already present in data mapping', async () => {
  await renderWithProviders(
    <Operations
      dataMapping={{
        ...dataMapping,
        operations: [{type: 'clean_html_tags'}],
      }}
      compatibleOperations={['clean_html_tags']}
      onOperationsChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.add'));

  const dropdown = screen.getByRole('listbox');

  expect(within(dropdown).getByText('akeneo.tailored_import.data_mapping.operations.no_result')).toBeInTheDocument();
  expect(
    within(dropdown).queryByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags')
  ).not.toBeInTheDocument();
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
    />
  );

  expect(
    screen.queryByText('akeneo.tailored_import.data_mapping.operations.unknown_operation')
  ).not.toBeInTheDocument();

  expect(mockedConsole).toHaveBeenCalledWith('No operation block found for operation type "unknown_operation"');
  mockedConsole.mockRestore();
});
