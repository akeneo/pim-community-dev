import {createDataMapping} from "./DataMapping";

const mockUuid = 'uuid';
jest.mock('akeneo-design-system', () => ({
  uuid: () => mockUuid,
}));

test('it create an attribute data mapping', () => {
  expect(createDataMapping('description', 'attribute')).toEqual({
    uuid: mockUuid,
    operations: [],
    sampleData: [],
    sources: [],
    target: {
      action: "set",
      channel: null,
      code: "description",
      ifEmpty: "skip",
      locale: null,
      onError: "skipLine",
      type: "attribute",
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
      action: "set",
      code: "family",
      ifEmpty: "skip",
      onError: "skipLine",
      type: "property",
    },
  });
});
