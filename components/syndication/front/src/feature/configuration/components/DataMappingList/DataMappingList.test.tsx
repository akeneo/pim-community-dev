import React from 'react';
import {screen, within} from '@testing-library/react';
import {DataMappingList} from './DataMappingList';
import {ValidationErrorsContext} from '../../contexts';
import {DataMapping} from '../../models/DataMapping';
import {renderWithProviders} from '../../tests';
import {RequirementsProvider} from '../../contexts/RequirementsContext';

test('it displays validation errors', async () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: '1',
      target: {
        name: 'my data mapping',
        type: 'string',
        required: false,
      },
      sources: [],
      format: {
        type: 'concat',
        elements: [],
        space_between: true,
      },
    },
  ];

  const requirements = [
    {
      code: 'my data mapping',
      type: 'string',
      required: false,
      label: 'My dataMapping',
      group: 'my group',
      help: 'My help',
    },
    {
      code: 'another data mapping',
      type: 'string',
      required: false,
      label: 'My dataMapping',
      group: 'my group',
      help: 'My help',
    },
  ];

  await renderWithProviders(
    <ValidationErrorsContext.Provider
      value={[
        {
          messageTemplate: 'akeneo.syndication.validation.data_mappings.target.max_length_reached',
          parameters: {
            '{{ value }}': 'way too long',
            '{{ limit }}': 255,
          },
          message: 'akeneo.syndication.validation.data_mappings.target.max_length_reached',
          propertyPath: '[data_mappings][1][target]',
          invalidValue: 'way too long',
        },
        {
          messageTemplate: 'akeneo.syndication.validation.data_mappings.max_data_mapping_count',
          parameters: {},
          message: 'akeneo.syndication.validation.data_mappings.max_data_mapping_count',
          propertyPath: '[data_mappings]',
          invalidValue: '',
        },
      ]}
    >
      <RequirementsProvider requirements={requirements}>
        <DataMappingList
          dataMappings={dataMappings}
          selectedRequirement={requirements[0].code}
          requirements={requirements}
          onDataMappingSelected={jest.fn()}
        />
      </RequirementsProvider>
    </ValidationErrorsContext.Provider>
  );

  const validationError = screen.getByText('akeneo.syndication.validation.data_mappings.target.max_length_reached');
  expect(validationError).toBeInTheDocument();

  const globalError = screen.getByText('akeneo.syndication.validation.data_mappings.max_data_mapping_count');
  expect(globalError).toBeInTheDocument();
});

test('it displays the sources labels on the row', async () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: '1',
      target: {
        name: 'my data mapping',
        type: 'string',
        required: false,
      },
      sources: [
        {
          uuid: '1234',
          code: 'name',
          type: 'attribute',
          locale: null,
          channel: null,
          operations: [],
          selection: {type: 'code'},
        },
        {
          uuid: '1235',
          code: 'parent',
          type: 'property',
          locale: null,
          channel: null,
          operations: {},
          selection: {type: 'code'},
        },
        {
          uuid: '31d80b71-b169-4275-81a3-c788690a5470',
          code: 'XSELL',
          type: 'association_type',
          locale: null,
          channel: null,
          operations: {},
          selection: {type: 'code', separator: ',', entity_type: 'products'},
        },
        {
          uuid: '296d487e-294b-4e42-a8d0-08e66f78a84d',
          code: 'UPSELL',
          type: 'association_type',
          locale: null,
          channel: null,
          operations: {},
          selection: {type: 'code', separator: ',', entity_type: 'products'},
        },
      ],
      format: {
        type: 'concat',
        elements: [],
        space_between: true,
      },
    },
    {
      uuid: '2',
      target: {
        name: 'another data mapping',
        type: 'string',
        required: false,
      },
      sources: [],
      format: {
        type: 'concat',
        elements: [],
        space_between: true,
      },
    },
  ];

  const requirements = [
    {
      code: 'my data mapping',
      type: 'string',
      required: false,
      label: 'My dataMapping',
      group: 'my group',
      help: 'My help',
    },
    {
      code: 'another data mapping',
      type: 'string',
      required: false,
      label: 'My dataMapping',
      group: 'my group',
      help: 'My help',
    },
  ];

  await renderWithProviders(
    <RequirementsProvider
      requirements={[
        {
          code: 'my data mapping',
          type: 'string',
          required: false,
          label: 'My dataMapping',
          group: 'my group',
          help: 'My help',
        },
        {
          code: 'another data mapping',
          type: 'string',
          required: false,
          label: 'My dataMapping',
          group: 'my group',
          help: 'My help',
        },
      ]}
    >
      <DataMappingList
        dataMappings={dataMappings}
        selectedRequirement={requirements[0].code}
        requirements={requirements}
        onDataMappingSelected={jest.fn()}
      />
    </RequirementsProvider>
  );

  const [, firstRow, secondRow] = screen.getAllByRole('row');

  expect(within(firstRow).getByText('English name, pim_common.parent, Cross sell, [UPSELL]')).toBeInTheDocument();
  expect(
    within(secondRow).getByText('akeneo.syndication.data_mapping_list.data_mapping_row.no_source')
  ).toBeInTheDocument();
});
