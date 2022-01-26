import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {Column, DataMapping} from 'feature';
import {DataMappingList} from './DataMappingList';

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
            code: 'a_code',
            type: 'property',
            action: 'set',
            ifEmpty: 'skip',
            onError: 'skipLine',
          },
          sources: ['source1'],
          operations: [],
          sampleData: [],
        })
      }
    >
      Add data mapping
    </button>
  ),
}));

test('it can add a new data mapping', async () => {
  const dataMappings: DataMapping[] = [];
  const columns: Column[] = [];
  const validationErrors: ValidationError[] = [];

  const handleDataMappingCreated = jest.fn();

  await renderWithProviders(
    <DataMappingList
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={validationErrors}
      onDataMappingAdded={handleDataMappingCreated}
    />
  );

  userEvent.click(screen.getByText('Add data mapping'));

  expect(handleDataMappingCreated).toBeCalledWith({
    uuid: 'value',
    target: {
      code: 'a_code',
      type: 'property',
      action: 'set',
      ifEmpty: 'skip',
      onError: 'skipLine',
    },
    sources: ['source1'],
    operations: [],
    sampleData: [],
  });
});

test('it displays the data mapping', async () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: 'value',
      target: {
        code: 'a_code',
        type: 'property',
        action: 'set',
        ifEmpty: 'skip',
        onError: 'skipLine',
      },
      sources: ['source1', 'source3'],
      operations: [],
      sampleData: [],
    },
    {
      uuid: 'anoter_value',
      target: {
        code: 'another_code',
        type: 'property',
        action: 'set',
        ifEmpty: 'skip',
        onError: 'skipLine',
      },
      sources: ['source2'],
      operations: [],
      sampleData: [],
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
  const validationErrors: ValidationError[] = [];

  await renderWithProviders(
    <DataMappingList
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={validationErrors}
      onDataMappingAdded={jest.fn()}
    />
  );

  expect(screen.getByText('a_code')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.sources: Source 1 (A) Source 3 (C)')
  ).toBeInTheDocument();
});

test('it displays validation errors', async () => {
  const dataMappings: DataMapping[] = [];
  const columns: Column[] = [];

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.name',
      invalidValue: '',
      message: 'this is a data mapping validation error',
      parameters: {},
      propertyPath: '[data_mapping][name]',
    },
  ];

  await renderWithProviders(
    <DataMappingList
      dataMappings={dataMappings}
      columns={columns}
      validationErrors={validationErrors}
      onDataMappingAdded={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.name')).toBeInTheDocument();
});
