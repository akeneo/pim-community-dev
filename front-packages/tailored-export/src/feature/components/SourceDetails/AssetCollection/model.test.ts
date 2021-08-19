import {isAssetCollectionSource, AssetCollectionSource} from './model';

const source: AssetCollectionSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code', separator: ';'},
};

test('it validates that something is an asset collection source', () => {
  expect(isAssetCollectionSource(source)).toEqual(true);

  expect(
    isAssetCollectionSource({
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
    isAssetCollectionSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
