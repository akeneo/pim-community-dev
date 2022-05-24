import React from 'react';
import {screen} from '@testing-library/react';
import {AttributeDataMappingDetails} from './AttributeDataMappingDetails';
import {renderWithProviders} from 'feature/tests';
import {Column, DataMapping} from 'feature/models';

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

const dataMapping: DataMapping = {
  uuid: 'mockUuid',
  operations: [],
  sample_data: [],
  sources: [],
  target: {
    attribute_type: 'pim_catalog_textarea',
    action_if_not_empty: 'set',
    channel: null,
    code: 'description',
    action_if_empty: 'skip',
    locale: null,
    type: 'attribute',
    source_configuration: null,
  },
};

test('it displays source configurator', async () => {
  await renderWithProviders(
    <AttributeDataMappingDetails
      dataMapping={dataMapping}
      columns={columns}
      validationErrors={[]}
      onTargetChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.sources.title')).toBeInTheDocument();
});

test('it displays attribute errors when attribute is not found', async () => {
  await renderWithProviders(
    <AttributeDataMappingDetails
      dataMapping={{
        ...dataMapping,
        target: {
          ...dataMapping.target,
          code: 'unknown',
        },
      }}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '[target][code]',
          invalidValue: '',
        },
      ]}
      columns={columns}
      onTargetChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.attribute_not_found')).toBeInTheDocument();
  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it renders nothing if the configurator is unknown', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  await renderWithProviders(
    <AttributeDataMappingDetails
      dataMapping={{
        ...dataMapping,
        target: {
          ...dataMapping.target,
          code: 'nothing',
        },
      }}
      columns={columns}
      validationErrors={[]}
      onTargetChange={jest.fn()}
      onRefreshSampleData={jest.fn()}
      onSourcesChange={jest.fn()}
      onOperationsChange={jest.fn()}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('No configurator found for "pim_catalog_nothing" attribute type');
  mockedConsole.mockRestore();
});
