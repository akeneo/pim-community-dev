import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from 'feature/tests';
import {ColumnConfiguration} from 'feature';
import {ColumnPreview} from './ColumnPreview';

const columnConfiguration: ColumnConfiguration = {
  uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
  sources: [
    {
      uuid: 'description-1e40-4c55-a415-89c7958b270d',
      code: 'description',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'parent-1e40-4c55-a415-89c7958b270d',
      code: 'parent',
      type: 'property',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'XSELL-1e40-4c55-a415-89c7958b270d',
      code: 'XSELL',
      type: 'association_type',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'code',
        separator: ',',
        entity_type: 'products',
      },
    },
  ],
  target: 'My column name',
  format: {
    type: 'concat',
    elements: [
      {
        type: 'source',
        uuid: 'description-1e40-4c55-a415-89c7958b270d',
        value: 'description-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'string',
        uuid: 'string-1e40-4c55-a415-89c7958b270d',
        value: 'some string',
      },
      {
        type: 'source',
        uuid: 'parent-1e40-4c55-a415-89c7958b270d',
        value: 'parent-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'string',
        uuid: 'another-string-1e40-4c55-a415-89c7958b270d',
        value: 'another string',
      },
      {
        type: 'source',
        uuid: 'XSELL-1e40-4c55-a415-89c7958b270d',
        value: 'XSELL-1e40-4c55-a415-89c7958b270d',
      },
    ],
  },
};

test('it renders the preview of a column', async () => {
  await renderWithProviders(<ColumnPreview columnConfiguration={columnConfiguration} />);

  expect(screen.getByText('English description')).toBeInTheDocument();
  expect(screen.getByText('some string')).toBeInTheDocument();
  expect(screen.getByText('pim_common.parent')).toBeInTheDocument();
  expect(screen.getByText('another string')).toBeInTheDocument();
  expect(screen.getByText('Cross sell')).toBeInTheDocument();
});

test('it throws when the source is not found', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const columnWithInvalidFormat: ColumnConfiguration = {
    ...columnConfiguration,
    format: {
      ...columnConfiguration.format,
      elements: [
        {
          type: 'source',
          uuid: 'invalid-uuid',
          value: 'invalid-uuid',
        },
      ],
    },
  };

  await expect(async () => {
    await renderWithProviders(<ColumnPreview columnConfiguration={columnWithInvalidFormat} />);
  }).rejects.toThrow();

  mockedConsole.mockRestore();
});
