import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from 'feature/tests';
import {Format, Source} from 'feature';
import {ColumnPreview} from './ColumnPreview';

const sources: Source[] = [
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
];

const format: Format = {
  type: 'concat',
  elements: [
    {
      type: 'source',
      uuid: 'description-1e40-4c55-a415-89c7958b270d',
      value: 'description-1e40-4c55-a415-89c7958b270d',
    },
    {
      type: 'text',
      uuid: 'text-1e40-4c55-a415-89c7958b270d',
      value: 'some text',
    },
    {
      type: 'source',
      uuid: 'parent-1e40-4c55-a415-89c7958b270d',
      value: 'parent-1e40-4c55-a415-89c7958b270d',
    },
    {
      type: 'text',
      uuid: 'another-text-1e40-4c55-a415-89c7958b270d',
      value: 'another text',
    },
    {
      type: 'source',
      uuid: 'XSELL-1e40-4c55-a415-89c7958b270d',
      value: 'XSELL-1e40-4c55-a415-89c7958b270d',
    },
  ],
};

test('it renders the preview of a column', async () => {
  await renderWithProviders(<ColumnPreview sources={sources} format={format} />);

  expect(screen.getByText('English description')).toBeInTheDocument();
  expect(screen.getByText('some text')).toBeInTheDocument();
  expect(screen.getByText('pim_common.parent')).toBeInTheDocument();
  expect(screen.getByText('another text')).toBeInTheDocument();
  expect(screen.getByText('Cross sell')).toBeInTheDocument();
});

test('it throws when the source is not found', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const invalidFormat: Format = {
    ...format,
    elements: [
      {
        type: 'source',
        uuid: 'invalid-uuid',
        value: 'invalid-uuid',
      },
    ],
  };

  await expect(async () => {
    await renderWithProviders(<ColumnPreview sources={sources} format={invalidFormat} />);
  }).rejects.toThrow();

  mockedConsole.mockRestore();
});

test('it throws when the source type is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  const invalidSources: Source[] = [
    {
      uuid: 'invalid-1e40-4c55-a415-89c7958b270d',
      code: 'invalid',
      // @ts-expect-error invalid source type
      type: 'invalid-type',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'code',
      },
    },
  ];

  const invalidFormat: Format = {
    ...format,
    elements: [
      {
        type: 'source',
        uuid: 'invalid-1e40-4c55-a415-89c7958b270d',
        value: 'invalid-1e40-4c55-a415-89c7958b270d',
      },
    ],
  };

  await expect(async () => {
    await renderWithProviders(<ColumnPreview sources={invalidSources} format={invalidFormat} />);
  }).rejects.toThrow();

  mockedConsole.mockRestore();
});
