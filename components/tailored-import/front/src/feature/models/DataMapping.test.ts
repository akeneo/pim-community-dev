import {channels} from 'feature/tests';
import {Attribute} from './Attribute';
import {
  DataMapping,
  updateDataMapping,
  createPropertyDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
  createAttributeDataMapping,
} from './DataMapping';

const mockUuid = 'uuid';
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  uuid: () => mockUuid,
}));

const attribute: Attribute = {
  code: 'description',
  localizable: false,
  scopable: false,
  is_locale_specific: false,
  available_locales: [],
  type: 'pim_catalog_text',
  labels: {},
};

test('it creates a default data mapping', () => {
  expect(createDefaultDataMapping([{uuid: 'columnUuid', index: 0, label: 'identifier'}])).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: ['columnUuid'],
    target: {
      action: 'set',
      channel: null,
      code: 'sku',
      ifEmpty: 'skip',
      locale: null,
      onError: 'skipLine',
      type: 'attribute',
    },
  });
});

test('it creates an attribute data mapping', () => {
  expect(createAttributeDataMapping('description', attribute, [])).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: 'set',
      channel: null,
      code: 'description',
      ifEmpty: 'skip',
      locale: null,
      onError: 'skipLine',
      type: 'attribute',
    },
  });
});

test('it creates a localizable & locale-specific attribute data mapping', () => {
  expect(
    createAttributeDataMapping(
      'description',
      {
        ...attribute,
        localizable: true,
        is_locale_specific: true,
        available_locales: ['fr_FR'],
      },
      channels
    )
  ).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: 'set',
      channel: null,
      code: 'description',
      ifEmpty: 'skip',
      locale: 'fr_FR',
      onError: 'skipLine',
      type: 'attribute',
    },
  });
});

test('it creates a property data mapping', () => {
  expect(createPropertyDataMapping('family')).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: 'set',
      code: 'family',
      ifEmpty: 'skip',
      onError: 'skipLine',
      type: 'property',
    },
  });
});

test('it adds a source to data mapping', () => {
  const dataMapping: DataMapping = {
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: 'set',
      channel: null,
      code: 'description',
      ifEmpty: 'skip',
      locale: null,
      onError: 'skipLine',
      type: 'attribute',
    },
  };

  expect(addSourceToDataMapping(dataMapping, {uuid: 'columnUuid', index: 0, label: 'identifier'})).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: ['columnUuid'],
    target: {
      action: 'set',
      channel: null,
      code: 'description',
      ifEmpty: 'skip',
      locale: null,
      onError: 'skipLine',
      type: 'attribute',
    },
  });
});

test('it updates a data mapping', () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: '8175126a-5deb-426c-a829-c9b7949dc1f7',
      operations: [],
      sampleData: [],
      sources: [],
      target: {
        action: 'set',
        channel: null,
        code: 'sku',
        ifEmpty: 'skip',
        locale: null,
        onError: 'skipLine',
        type: 'attribute',
      },
    },
    {
      uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
      operations: [],
      sampleData: [],
      sources: [],
      target: {
        action: 'set',
        channel: null,
        code: 'description',
        ifEmpty: 'clear',
        locale: null,
        onError: 'skipValue',
        type: 'attribute',
      },
    },
  ];

  const updatedDataMapping: DataMapping = {
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: 'set',
      channel: null,
      code: 'description',
      ifEmpty: 'skip',
      locale: null,
      onError: 'skipLine',
      type: 'attribute',
    },
  };

  const nonExistentDataMapping = {...updatedDataMapping, uuid: '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab'};

  expect(updateDataMapping([], updatedDataMapping)).toEqual([]);
  expect(updateDataMapping(dataMappings, updatedDataMapping)).toEqual([dataMappings[0], updatedDataMapping]);
  expect(updateDataMapping(dataMappings, nonExistentDataMapping)).toEqual(dataMappings);
});
