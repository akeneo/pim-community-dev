import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../../../tests';
import {Format, Source} from '../../../../models';
import {DataMappingPreview} from './DataMappingPreview';

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
  space_between: true,
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

const getByTextContent = (textMatch: string | RegExp): HTMLElement =>
  screen.getByText((_content, node) => {
    const hasText = (node: Element) => node.textContent === textMatch;
    const nodeHasText = hasText(node as Element);
    const childrenDontHaveText = Array.from(node?.children || []).every(child => !hasText(child));

    return nodeHasText && childrenDontHaveText;
  });

test('it renders the preview of a data mapping with space between sources', async () => {
  await renderWithProviders(<DataMappingPreview sources={sources} format={format} />);

  expect(
    getByTextContent('English description some text pim_common.parent another text Cross sell')
  ).toBeInTheDocument();
});

test('it renders the preview of a data mapping without space between sources', async () => {
  await renderWithProviders(<DataMappingPreview sources={sources} format={{...format, space_between: false}} />);

  expect(getByTextContent('English descriptionsome textpim_common.parentanother textCross sell')).toBeInTheDocument();
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
    await renderWithProviders(<DataMappingPreview sources={sources} format={invalidFormat} />);
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
    await renderWithProviders(<DataMappingPreview sources={invalidSources} format={invalidFormat} />);
  }).rejects.toThrow();

  mockedConsole.mockRestore();
});
