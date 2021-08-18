import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from 'feature/tests';
import {Format, Source} from 'feature';
import {ConcatElementList} from './ConcatElementList';

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

test('it renders the a list of concat elements', async () => {
  await renderWithProviders(
    <ConcatElementList
      validationErrors={[]}
      sources={sources}
      format={format}
      onConcatElementChange={jest.fn()}
      onConcatElementReorder={jest.fn()}
      onConcatElementRemove={jest.fn()}
    />
  );

  expect(screen.getByText('English description')).toBeInTheDocument();
  expect(screen.getByDisplayValue('some text')).toBeInTheDocument();
  expect(screen.getByText('pim_common.parent')).toBeInTheDocument();
  expect(screen.getByDisplayValue('another text')).toBeInTheDocument();
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
    await renderWithProviders(
      <ConcatElementList
        validationErrors={[]}
        sources={sources}
        format={invalidFormat}
        onConcatElementChange={jest.fn()}
        onConcatElementReorder={jest.fn()}
        onConcatElementRemove={jest.fn()}
      />
    );
  }).rejects.toThrow();

  mockedConsole.mockRestore();
});
