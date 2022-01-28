import {DataMapping, updateDataMapping} from '.';
import {createDataMapping, createDefaultDataMapping, addSourceToDataMapping} from './DataMapping';

const mockUuid = 'uuid';
jest.mock('akeneo-design-system', () => ({
  uuid: () => mockUuid,
}));

test('it create a default data mapping', () => {
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

test('it create an attribute data mapping', () => {
  expect(createDataMapping('description', 'attribute')).toEqual({
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

test('it create a property data mapping', () => {
  expect(createDataMapping('family', 'property')).toEqual({
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

test('it add a source to data mapping', () => {
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


test('it update a data mapping', () => {
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
