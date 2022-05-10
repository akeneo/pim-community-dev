import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {DataMappingDetails} from './DataMappingDetails';
import {AttributeTarget, Column, DataMapping, FileStructure, PropertyTarget} from '../../models';

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
    source_configuration: null,
  },
  sources: ['dba0d9f8-2283-4a07-82b7-67e0435b7dcc'],
  operations: [],
  sample_data: ['product_1', 'product_2', 'product_3'],
};

const propertyDataMapping: DataMapping = {
  uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
  target: {
    code: 'categories',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    source_configuration: null,
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
    source_configuration: null,
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
  SourceDropdown: ({onColumnSelected}: {onColumnSelected: (selectedColumn: Column) => void}) => (
    <button
      onClick={() =>
        onColumnSelected({uuid: 'dba0d9f8-2283-4a07-82b7-67e0435b7dcc', index: 0, label: 'Product identifier'})
      }
    >
      Add source
    </button>
  ),
}));

jest.mock('../../hooks/useSampleDataFetcher', () => ({
  useSampleDataFetcher: () => async () => {
    return ['product_1', 'product_2', 'product_3'];
  },
}));

jest.mock('../../hooks/useRefreshedSampleDataFetcher', () => ({
  useRefreshedSampleDataFetcher: () => async () => {
    return 'product_4';
  },
}));

test('it displays a property data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails
      dataMapping={propertyDataMapping}
      fileKey="/file_key"
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
});

test('it displays an attribute data mapping', async () => {
  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMapping}
      fileKey="/file_key"
      fileStructure={fileStructure}
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.title')).toBeInTheDocument();
  expect(screen.getByText('Name (B)')).toBeInTheDocument();
});

test('it can change attribute target parameters', async () => {
  const handleDataMappingChange = jest.fn();

  const attributeTarget: AttributeTarget = {
    code: 'name',
    type: 'attribute',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    channel: 'print',
    locale: 'en_US',
    source_configuration: null,
  };

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={{...attributeDataMapping, target: attributeTarget}}
      fileKey="/file_key"
      fileStructure={fileStructure}
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

test('it can change property target parameters', async () => {
  const handleDataMappingChange = jest.fn();

  const propertyTarget: PropertyTarget = {
    code: 'categories',
    type: 'property',
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={{...propertyDataMapping, target: propertyTarget}}
      fileKey="/file_key"
      fileStructure={fileStructure}
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...propertyDataMapping,
    target: {
      ...propertyTarget,
      action_if_empty: 'clear',
    },
  });
});

test('it can add a source to a data mapping', async () => {
  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMappingWithoutSource}
      fileKey="/file_key"
      fileStructure={fileStructure}
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
      fileKey="/file_key"
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

test('it can add an operation', async () => {
  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMapping}
      fileKey="/file_key"
      fileStructure={fileStructure}
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.add'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.operations.clean_html_tags.title'));

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...attributeDataMapping,
    operations: [
      {
        type: 'clean_html_tags',
      },
    ],
  });
});

test('it can refresh a sample data', async () => {
  const handleDataMappingChange = jest.fn();

  await renderWithProviders(
    <DataMappingDetails
      dataMapping={attributeDataMapping}
      fileKey="/file_key"
      fileStructure={fileStructure}
      columns={columns}
      validationErrors={[]}
      onDataMappingChange={handleDataMappingChange}
    />
  );

  expect(screen.queryByText('product_1')).toBeInTheDocument();
  expect(screen.queryByText('product_2')).toBeInTheDocument();
  expect(screen.queryByText('product_3')).toBeInTheDocument();
  expect(screen.queryByText('product_4')).not.toBeInTheDocument();

  await act(async () => {
    await userEvent.click(screen.getAllByTitle('akeneo.tailored_import.data_mapping.preview.refresh')[0]);
  });

  expect(handleDataMappingChange).toHaveBeenCalledWith({
    ...attributeDataMapping,
    sample_data: ['product_4', 'product_2', 'product_3'],
  });
});
