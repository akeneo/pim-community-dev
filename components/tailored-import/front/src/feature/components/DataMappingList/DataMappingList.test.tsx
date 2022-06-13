import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping} from 'feature';
import {renderWithProviders} from 'feature/tests';
import {DataMappingList} from './DataMappingList';

jest.mock('../AddDataMappingDropdown', () => ({
  AddDataMappingDropdown: ({onDataMappingAdded}: {onDataMappingAdded: (dataMapping: DataMapping) => void}) => (
    <button
      onClick={() =>
        onDataMappingAdded({
          uuid: 'value',
          target: {
            code: 'categories',
            type: 'property',
            action_if_not_empty: 'set',
            action_if_empty: 'skip',
          },
          sources: ['source1'],
          operations: [],
          sample_data: [],
        })
      }
    >
      Add data mapping
    </button>
  ),
}));

const dataMappings: DataMapping[] = [
  {
    uuid: 'uuid-parent',
    target: {
      code: 'parent',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    },
    sources: ['source1'],
    operations: [],
    sample_data: [],
  },
  {
    uuid: 'uuid-categories',
    target: {
      code: 'categories',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    },
    sources: ['source2', 'source3'],
    operations: [],
    sample_data: [],
  },
];

const columns: Column[] = [
  {
    uuid: 'source1',
    index: 0,
    label: 'parent',
  },
  {
    uuid: 'source2',
    index: 1,
    label: 'catego 1',
  },
  {
    uuid: 'source3',
    index: 2,
    label: 'catego 2',
  },
];

test('it can add a new data mapping', async () => {
  const handleDataMappingCreated = jest.fn();

  await renderWithProviders(
    <DataMappingList
      selectedDataMappingUuid={null}
      onDataMappingSelected={jest.fn()}
      dataMappings={[]}
      columns={[]}
      validationErrors={[]}
      onDataMappingAdded={handleDataMappingCreated}
      onDataMappingRemoved={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('Add data mapping'));

  expect(handleDataMappingCreated).toBeCalledWith({
    uuid: 'value',
    target: {
      code: 'categories',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    },
    sources: ['source1'],
    operations: [],
    sample_data: [],
  });
});

test('it displays the data mappings', async () => {
  await renderWithProviders(
    <DataMappingList
      selectedDataMappingUuid={null}
      onDataMappingSelected={jest.fn()}
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={[]}
      onDataMappingAdded={jest.fn()}
      onDataMappingRemoved={jest.fn()}
    />
  );

  expect(screen.getByText('pim_common.categories')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.sources.title: catego 1 (B) catego 2 (C)')
  ).toBeInTheDocument();
});

test('it calls handler when row is selected', async () => {
  const handleDataMappingSelected = jest.fn();

  await renderWithProviders(
    <DataMappingList
      selectedDataMappingUuid={null}
      onDataMappingSelected={handleDataMappingSelected}
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={[]}
      onDataMappingAdded={jest.fn()}
      onDataMappingRemoved={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('pim_common.parent'));

  expect(handleDataMappingSelected).toBeCalledWith('uuid-parent');
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.name',
      invalidValue: '',
      message: 'this is a data mapping validation error',
      parameters: {},
      propertyPath: '[uuid-parent]',
    },
    {
      messageTemplate: 'global_error.key.name',
      invalidValue: '',
      message: 'this is a global data mapping validation error',
      parameters: {},
      propertyPath: '',
    },
  ];

  await renderWithProviders(
    <DataMappingList
      selectedDataMappingUuid={null}
      onDataMappingSelected={jest.fn()}
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={validationErrors}
      onDataMappingAdded={jest.fn()}
      onDataMappingRemoved={jest.fn()}
    />
  );

  expect(screen.getByText('global_error.key.name')).toBeInTheDocument();
  expect(screen.queryByText('error.key.name')).not.toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
});

test('it can search data mappings on column labels', async () => {
  jest.useFakeTimers();

  await renderWithProviders(
    <DataMappingList
      selectedDataMappingUuid={null}
      onDataMappingSelected={jest.fn()}
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={[]}
      onDataMappingAdded={jest.fn()}
      onDataMappingRemoved={jest.fn()}
    />
  );

  act(() => {
    userEvent.paste(screen.getByPlaceholderText('pim_common.search'), 'cate');
    jest.runAllTimers();
  });

  expect(screen.getByText('pim_common.categories')).toBeInTheDocument();
  expect(screen.queryByText('pim_common.parent')).not.toBeInTheDocument();
});
