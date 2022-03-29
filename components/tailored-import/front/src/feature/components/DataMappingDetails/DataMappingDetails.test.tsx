import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {DataMappingDetails} from './DataMappingDetails';
import {AttributeTarget, Column, DataMapping, FileStructure} from '../../models';

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
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    source_parameter: null,
  },
  sources: ['dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
  operations: [],
  sample_data: [],
};

const propertyDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'family',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    source_parameter: null,
  },
  sources: ['288d85cb-3ffb-432d-a422-d2c6810113ab'],
  operations: [],
  sample_data: [],
};

const attributeDataMappingWithoutSource: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'name',
    type: 'attribute',
    channel: null,
    locale: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    source_parameter: null,
  },
  sources: [],
  operations: [],
  sample_data: [],
};

const fileStructure: FileStructure = {
  header_row: 1,
  first_column: 1,
  first_product_row: 2,
  unique_identifier_column: 1,
  sheet_name: 'sheet_1',
};

jest.mock('./SourceDropdown', () => ({
  SourceDropdown: ({
    onColumnSelected,
    disabled,
  }: {
    onColumnSelected: (selectedColumn: Column) => void;
    disabled: boolean;
  }) => (
    <button
      onClick={() =>
        onColumnSelected({uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc', index: 0, label: 'Product identifier'})
      }
    >
      Add source
    </button>
  ),
}));

jest.mock('../../hooks/useFetchSampleData', () => ({
  useFetchSampleData: () => async () => {
    return ['product_1', 'product_2', 'product_3'];
  },
}));

test('it displays a property data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails
      dataMapping={propertyDataMapping}
      fileKey={'/file_key'}
      fileStructure={
        {
          header_row: 1,
          first_column: 1,
          first_product_row: 2,
          unique_identifier_column: 1,
          sheet_name: 'sheet_1',
        } as FileStructure
      }
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.title')).toBeInTheDocument();
  expect(screen.getByText('Product identifier (A)')).toBeInTheDocument();
});

test('it displays an attribute data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMapping}
      fileKey={'/file_key'}
      fileStructure={ fileStructure }
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.title')).toBeInTheDocument();
  expect(screen.getByText('Name (B)')).toBeInTheDocument();
});

test('it can change target parameters', async () => {
  const handleDataMappingChange = jest.fn();

  const attributeTarget: AttributeTarget = {
    code: 'name',
    type: 'attribute',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    channel: 'print',
    locale: 'en_US',
    source_parameter: null,
  };

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={{...attributeDataMapping, target: attributeTarget}}
      fileKey={'/file_key'}
      fileStructure={ fileStructure }
      columns={columns}
      validationErrors={[]}
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
      dataMapping={attributeDataMappingWithoutSource}
      fileKey={'/file_key'}
      fileStructure={ fileStructure }
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  await userEvent.click(screen.getByText('Add source'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...attributeDataMappingWithoutSource,
    sources: ['dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
    sample_data: ['product_1', 'product_2', 'product_3'],
  });
});

test('it can remove a source', async () => {
  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMapping}
      fileKey={'/file_key'}
      fileStructure={fileStructure}
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...attributeDataMappingWithoutSource,
    sources: [],
    sample_data: [],
  });
});
