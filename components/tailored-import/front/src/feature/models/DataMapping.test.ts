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
  const columnIdentifier = {uuid: 'columnUuid', index: 0, label: 'identifier'};
  const identifierAttribute: Attribute = {
    code: 'sku',
    type: 'pim_catalog_identifier',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  };

  expect(createDefaultDataMapping(identifierAttribute, columnIdentifier, [])).toEqual({
    uuid: mockUuid,
    operations: [],
    sample_data: [],
    sources: ['columnUuid'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'sku',
      action_if_empty: 'skip',
      source_parameter: null,
      locale: null,
      type: 'attribute',
    },
  });
});

test('it creates a default data mapping with sample data', () => {
  const columnIdentifier = {uuid: 'columnUuid', index: 0, label: 'identifier'};
  const identifierAttribute: Attribute = {
    code: 'sku',
    type: 'pim_catalog_identifier',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  };
  const sampleData = ['value1', 'value2', 'value3'];

  expect(createDefaultDataMapping(identifierAttribute, columnIdentifier, sampleData)).toEqual({
    uuid: mockUuid,
    operations: [],
    sample_data: sampleData,
    sources: ['columnUuid'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'sku',
      action_if_empty: 'skip',
      source_parameter: null,
      locale: null,
      type: 'attribute',
    },
  });
});

test('it creates an attribute data mapping', () => {
  expect(createAttributeDataMapping(attribute, [])).toEqual({
    uuid: mockUuid,
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      source_parameter: null,
      locale: null,
      type: 'attribute',
    },
  });
});

test('it creates a localizable & locale-specific attribute data mapping', () => {
  expect(
    createAttributeDataMapping(
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
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      source_parameter: null,
      locale: 'fr_FR',
      type: 'attribute',
    },
  });
});

test('it creates a property data mapping', () => {
  expect(createPropertyDataMapping('family')).toEqual({
    uuid: mockUuid,
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      code: 'family',
      action_if_empty: 'skip',
      type: 'property',
    },
  });
});

test('it adds a source to data mapping', () => {
  const dataMapping: DataMapping = {
    uuid: mockUuid,
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_parameter: null,
    },
  };

  expect(addSourceToDataMapping(dataMapping, {uuid: 'columnUuid', index: 0, label: 'identifier'})).toEqual({
    uuid: mockUuid,
    operations: [],
    sample_data: [],
    sources: ['columnUuid'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_parameter: null,
    },
  });
});

test('it updates a data mapping', () => {
  const dataMappings: DataMapping[] = [
    {
      uuid: '8175126a-5deb-426c-a829-c9b7949dc1f7',
      operations: [],
      sample_data: [],
      sources: [],
      target: {
        action_if_not_empty: 'set',
        channel: null,
        code: 'sku',
        action_if_empty: 'skip',
        locale: null,
        type: 'attribute',
        source_parameter: null,
      },
    },
    {
      uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
      operations: [],
      sample_data: [],
      sources: [],
      target: {
        action_if_not_empty: 'set',
        channel: null,
        code: 'description',
        action_if_empty: 'clear',
        locale: null,
        type: 'attribute',
        source_parameter: null,
      },
    },
  ];

  const updatedDataMapping: DataMapping = {
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_parameter: null,
    },
  };

  const nonExistentDataMapping = {...updatedDataMapping, uuid: '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab'};

  expect(updateDataMapping([], updatedDataMapping)).toEqual([]);
  expect(updateDataMapping(dataMappings, updatedDataMapping)).toEqual([dataMappings[0], updatedDataMapping]);
  expect(updateDataMapping(dataMappings, nonExistentDataMapping)).toEqual(dataMappings);
});
