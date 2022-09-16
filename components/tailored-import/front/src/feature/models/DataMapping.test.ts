import {channels} from 'feature/tests';
import {Attribute} from './Attribute';
import {
  DataMapping,
  updateDataMapping,
  createPropertyDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
  createAttributeDataMapping,
  countMappedColumnsInDataMappings,
} from './DataMapping';

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
    uuid: expect.any(String),
    operations: [],
    sample_data: [],
    sources: ['columnUuid'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'sku',
      action_if_empty: 'skip',
      source_configuration: null,
      locale: null,
      type: 'attribute',
      attribute_type: 'pim_catalog_identifier',
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
    uuid: expect.any(String),
    operations: [],
    sample_data: sampleData,
    sources: ['columnUuid'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'sku',
      action_if_empty: 'skip',
      source_configuration: null,
      locale: null,
      type: 'attribute',
      attribute_type: 'pim_catalog_identifier',
    },
  });
});

test('it creates an attribute data mapping', () => {
  expect(createAttributeDataMapping(attribute, [])).toEqual({
    uuid: expect.any(String),
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      source_configuration: null,
      locale: null,
      type: 'attribute',
      attribute_type: 'pim_catalog_text',
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
    uuid: expect.any(String),
    operations: [],
    sample_data: [],
    sources: [],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'description',
      action_if_empty: 'skip',
      source_configuration: null,
      locale: 'fr_FR',
      type: 'attribute',
      attribute_type: attribute.type,
    },
  });
});

test('it creates a property data mapping', () => {
  expect(createPropertyDataMapping('family')).toEqual({
    uuid: expect.any(String),
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
    uuid: expect.any(String),
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
      attribute_type: 'pim_catalog_text',
      source_configuration: null,
    },
  };

  expect(addSourceToDataMapping(dataMapping, {uuid: 'columnUuid', index: 0, label: 'identifier'})).toEqual({
    ...dataMapping,
    sources: ['columnUuid'],
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
        attribute_type: 'pim_catalog_identifier',
        action_if_empty: 'skip',
        locale: null,
        type: 'attribute',
        source_configuration: null,
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
        attribute_type: 'pim_catalog_text',
        action_if_empty: 'clear',
        locale: null,
        type: 'attribute',
        source_configuration: null,
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
      attribute_type: 'pim_catalog_text',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_configuration: null,
    },
  };

  const nonExistentDataMapping = {...updatedDataMapping, uuid: '68abfdcb-c91e-40e4-a928-fdfa7a31e8ab'};

  expect(updateDataMapping([], updatedDataMapping)).toEqual([]);
  expect(updateDataMapping(dataMappings, updatedDataMapping)).toEqual([dataMappings[0], updatedDataMapping]);
  expect(updateDataMapping(dataMappings, nonExistentDataMapping)).toEqual(dataMappings);
});

test('it counts mapped columns inside data mappings', () => {
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
        attribute_type: 'pim_catalog_identifier',
        action_if_empty: 'skip',
        locale: null,
        type: 'attribute',
        source_configuration: null,
      },
    },
    {
      uuid: '8175126a-5deb-426c-a829-c9b7949dc1f8',
      operations: [],
      sample_data: [],
      sources: ['5f942671-3ba3-4639-b96b-245a720ea587', '5f942671-3ba3-4639-b96b-245a720ea588'],
      target: {
        action_if_not_empty: 'set',
        channel: null,
        code: 'colors',
        attribute_type: 'pim_catalog_multiselect',
        action_if_empty: 'skip',
        locale: null,
        type: 'attribute',
        source_configuration: null,
      },
    },
    {
      uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
      operations: [],
      sample_data: [],
      sources: ['5f942671-3ba3-4639-b96b-245a720ea587', '5f942671-3ba3-4639-b96b-245a720ea589'],
      target: {
        action_if_not_empty: 'set',
        channel: null,
        code: 'brands',
        attribute_type: 'pim_catalog_multiselect',
        action_if_empty: 'clear',
        locale: null,
        type: 'attribute',
        source_configuration: null,
      },
    },
  ];

  expect(countMappedColumnsInDataMappings(dataMappings)).toEqual(3);
  expect(countMappedColumnsInDataMappings([])).toEqual(0);
});
