import {isReferenceEntityCollectionSource, ReferenceEntityCollectionSource} from './model';

const source: ReferenceEntityCollectionSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code', separator: ';'},
};

test('it validates that something is a reference entity collection source', () => {
  expect(isReferenceEntityCollectionSource(source)).toEqual(true);

  expect(
    isReferenceEntityCollectionSource({
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
    isReferenceEntityCollectionSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
