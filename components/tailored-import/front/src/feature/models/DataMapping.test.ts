import {channels} from 'feature/tests';
import {Attribute} from './Attribute';
import {Column} from './Column';
import {
  DataMapping,
  updateDataMapping,
  createPropertyDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
  createAttributeDataMapping,
  filterOnColumnLabels,
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

test('it can filter data mappings based on column labels', () => {
  const columns: Column[] = [
    {uuid: 'uuid-IDEnTiFier', index: 0, label: 'IDEnTiFier'},
    {uuid: 'uuid-Name', index: 1, label: 'Name'},
    {uuid: 'uuid-idendescrip tion', index: 2, label: 'idendescrip tion'},
    {uuid: 'uuid-catego1', index: 2, label: 'catego1'},
    {uuid: 'uuid-catego2', index: 2, label: 'catego2 tion'},
  ];

  const identifierDataMapping = {...createAttributeDataMapping(attribute, []), sources: ['uuid-IDEnTiFier']};
  const nameDataMapping = {...createAttributeDataMapping(attribute, []), sources: ['uuid-Name']};
  const descriptionDataMapping = {...createAttributeDataMapping(attribute, []), sources: ['uuid-idendescrip tion']};
  const categoriesDataMapping = {...createPropertyDataMapping('categories'), sources: ['uuid-catego1', 'uuid-catego2']};

  const dataMappings: DataMapping[] = [
    identifierDataMapping,
    nameDataMapping,
    descriptionDataMapping,
    categoriesDataMapping,
  ];

  expect(filterOnColumnLabels(dataMappings, columns, '')).toEqual(dataMappings);
  expect(filterOnColumnLabels(dataMappings, columns, 'iden')).toEqual([identifierDataMapping, descriptionDataMapping]);
  expect(filterOnColumnLabels(dataMappings, columns, 'cat')).toEqual([categoriesDataMapping]);
  expect(filterOnColumnLabels(dataMappings, columns, ' TION')).toEqual([descriptionDataMapping, categoriesDataMapping]);
  expect(filterOnColumnLabels(dataMappings, columns, 'not found')).toEqual([]);
});
