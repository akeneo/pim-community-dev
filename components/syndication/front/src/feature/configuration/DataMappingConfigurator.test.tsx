import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {DataMappingConfigurator} from './DataMappingConfigurator';
import {DataMapping} from './models/DataMapping';
import {renderWithProviders} from './tests';

jest.mock('./hooks/pim/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => () => ({
    results: [
      {
        code: 'system',
        label: 'System',
        children: [
          {
            code: 'category',
            label: 'Categories',
            type: 'property',
          },
          {
            code: 'enabled',
            label: 'Activé',
            type: 'property',
          },
        ],
      },
      {
        code: 'marketing',
        label: 'Marketing',
        children: [
          {
            code: 'name',
            label: 'Nom',
            type: 'attribute',
          },
          {
            code: 'description',
            label: 'Description',
            type: 'attribute',
          },
        ],
      },
    ],
  }),
}));

test('It adds source when user click on add source', async () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: {
        name: 'my dataMapping',
        type: 'string',
        required: false,
      },
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ];
  const requirements = [
    {
      code: 'my dataMapping',
      type: 'string',
      required: false,
      label: 'My dataMapping',
      group: 'my group',
      help: 'My help',
    },
  ];

  const handleDataMappingsConfigurationChange = jest.fn();

  await renderWithProviders(
    <DataMappingConfigurator
      dataMappings={dataMappings}
      onDataMappingsConfigurationChange={handleDataMappingsConfigurationChange}
      requirements={requirements}
    />
  );

  const addSourceButton = screen.getByText('akeneo.syndication.data_mapping_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Description'));
  });

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith([
    {
      target: {
        name: 'my dataMapping',
        type: 'string',
        required: false,
      },
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      sources: [
        {
          channel: 'ecommerce',
          code: 'description',
          locale: 'en_US',
          operations: {},
          selection: {
            type: 'code',
          },
          type: 'attribute',
          uuid: expect.any(String),
        },
      ],
      format: {
        elements: [
          {
            type: 'source',
            uuid: expect.any(String),
            value: expect.any(String),
          },
        ],
        type: 'concat',
        space_between: true,
      },
    },
  ]);
});
