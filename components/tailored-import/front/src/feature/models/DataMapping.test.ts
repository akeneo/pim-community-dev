import {DataMapping} from '.';
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
