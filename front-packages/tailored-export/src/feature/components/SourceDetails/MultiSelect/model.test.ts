import {isMultiSelectSource, MultiSelectSource} from './model';

const source: MultiSelectSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code', separator: ','},
};

test('it validates that something is a MultiSelect source', () => {
  expect(isMultiSelectSource(source)).toEqual(true);

  expect(
    isMultiSelectSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  expect(
    isMultiSelectSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
