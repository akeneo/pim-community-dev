import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {DataMappingDetails} from './DataMappingDetails';
import {AttributeTarget, Column, DataMapping} from '../../models';

const columns: Column[] = [
  {
    uuid: '288d85cb-3ffb-432d-a422-d2c6810113ab',
    index: 0,
    label: 'Product identifier',
  },
  {
    uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
    index: 1,
    label: 'Name',
  },
];

const attributeDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    if_empty: 'skip',
  },
  sources: ['dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
  operations: [],
  sample_data: [],
};

const propertyDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    if_empty: 'skip',
  },
  sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab'],
  operations: [],
  sample_data: [],
};

jest.mock('../SourceDropdown', () => ({
  SourceDropdown: ({
    onColumnSelected,
    disabled,
  }: {
    onColumnSelected: (selectedColumn: Column) => void;
    disabled: boolean;
  }) => (
    <button
      onClick={
        disabled
          ? undefined
          : () =>
              onColumnSelected({uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc', index: 0, label: 'Product identifier'})
      }
    >
      Add source
    </button>
  ),
}));

test('it displays a property data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails dataMapping={propertyDataMapping} columns={columns} onDataMappingChange={jest.fn()} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.sources')).toBeInTheDocument();
  expect(screen.getByText('Product identifier (A)')).toBeInTheDocument();
});

test('it displays an attribute data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails dataMapping={attributeDataMapping} columns={columns} onDataMappingChange={jest.fn()} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.sources')).toBeInTheDocument();
  expect(screen.getByText('Name (B)')).toBeInTheDocument();
});

test('it can change target parameters', async () => {
  const handleDataMappingChange = jest.fn();

  const attributeTarget: AttributeTarget = {
    code: 'name',
    type: 'attribute',
    action: 'set',
    if_empty: 'skip',
    channel: 'print',
    locale: 'en_US',
  };

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={{...attributeDataMapping, target: attributeTarget}}
      columns={columns}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[ecommerce]'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...attributeDataMapping,
    target: {
      ...attributeTarget,
      channel: 'ecommerce',
    },
  });
});

test('it can add a source to a data mapping', async () => {
  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={propertyDataMapping}
      columns={columns}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  userEvent.click(screen.getByText('Add source'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...propertyDataMapping,
    sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab', 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
  });
});

test('it cannot add a source to a data mapping when limit is reached', async () => {
  const dataMapping = {
    ...propertyDataMapping,
    sources: [
      '288d85cb-3ffb-432d-a422-d2c6810113ab',
      'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
      '288d85cb-3ffb-432d-a422-d2c6810113ab',
      'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
    ],
  };

  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails dataMapping={dataMapping} columns={columns} onDataMappingChange={handleDataMappingChange} />
  );

  userEvent.click(screen.getByText('Add source'));

  expect(handleDataMappingChange).not.toHaveBeenCalled();
});
