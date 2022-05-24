import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping} from 'feature';
import {DataMappingList} from './DataMappingList';
import {renderWithProviders} from 'feature/tests';

const mockUuid = 'd1249682-720e-11ec-90d6-0242ac120003';
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => mockUuid,
}));

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
    uuid: '288d85cb-3ffb-432d-a422-d2c6810113ab',
    target: {
      code: 'parent',
      type: 'property',
      action_if_not_empty: 'set',
      action_if_empty: 'skip',
    },
    sources: ['source1', 'source3'],
    operations: [],
    sample_data: [],
  },
];

const columns: Column[] = [
  {
    uuid: 'source1',
    index: 0,
    label: 'Source 1',
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

test('it displays the data mapping', async () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: 'value',
      target: {
        code: 'parent',
        type: 'property',
        action_if_not_empty: 'set',
        action_if_empty: 'skip',
      },
      sources: ['source1', 'source3'],
      operations: [],
      sample_data: [],
    },
    {
      uuid: 'another_value',
      target: {
        code: 'family',
        type: 'property',
        action_if_not_empty: 'set',
        action_if_empty: 'skip',
      },
      sources: ['source2'],
      operations: [],
      sample_data: [],
    },
  ];
  const columns: Column[] = [
    {
      uuid: 'source1',
      index: 0,
      label: 'Source 1',
    },
    {
      uuid: 'source2',
      index: 1,
      label: 'Source 2',
    },
    {
      uuid: 'source3',
      index: 2,
      label: 'Source 3',
    },
  ];

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

  expect(screen.getByText('pim_common.parent')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.sources.title: Source 1 (A) Source 3 (C)')
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

  expect(handleDataMappingSelected).toBeCalledWith('288d85cb-3ffb-432d-a422-d2c6810113ab');
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.name',
      invalidValue: '',
      message: 'this is a data mapping validation error',
      parameters: {},
      propertyPath: '[288d85cb-3ffb-432d-a422-d2c6810113ab]',
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
