import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingDetails} from './DataMappingDetails';
import {Column, DataMapping} from "../../models";
import userEvent from "@testing-library/user-event";

const columns: Column[] = [
  {
    uuid: '288d85cb-3ffb-432d-a422-d2c6810113ab',
    index: 0,
    label: 'Product identifier'
  },
  {
    uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc',
    index: 1,
    label: 'Name'
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
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: ['dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
  operations: [],
  sampleData: [],
};

const propertyDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  },
  sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab'],
  operations: [],
  sampleData: [],
};

jest.mock('../SourceDropdown', () => ({
  SourceDropdown: ({onColumnSelected, disabled}: {onColumnSelected: (selectedColumn: Column) => void, disabled: boolean}) => (
    <button onClick={disabled ? undefined : () => onColumnSelected({uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc', index: 0, label: 'Product identifier'})}>
      Add source
    </button>
  ),
}));

test('it display a property data mapping', () => {
  renderWithProviders(
    <DataMappingDetails dataMapping={propertyDataMapping} columns={columns} onDataMappingChange={jest.fn()}/>
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.sources')).toBeInTheDocument();
  expect(screen.getByText('Product identifier (A)')).toBeInTheDocument();
});

test('it display an attribute data mapping', () => {
  renderWithProviders(
    <DataMappingDetails dataMapping={attributeDataMapping} columns={columns} onDataMappingChange={jest.fn()}/>
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.sources')).toBeInTheDocument();
  expect(screen.getByText('Name (B)')).toBeInTheDocument();
});

test('it can add a source to a data mapping', async () => {
  const handleDataMappingChange = jest.fn();
  renderWithProviders(
    <DataMappingDetails dataMapping={propertyDataMapping} columns={columns} onDataMappingChange={handleDataMappingChange}/>
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
      'dba0d9f8-2283-4a07-82b7-67e0435b7dcc'
    ],
  };

  const handleDataMappingChange = jest.fn();
  renderWithProviders(
    <DataMappingDetails dataMapping={dataMapping} columns={columns} onDataMappingChange={handleDataMappingChange}/>
  );

  userEvent.click(screen.getByText('Add source'));

  expect(handleDataMappingChange).not.toHaveBeenCalled();
});
