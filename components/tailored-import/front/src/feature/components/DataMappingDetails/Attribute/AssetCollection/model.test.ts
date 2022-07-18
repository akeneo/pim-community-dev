import {isAssetCollectionTarget, AssetCollectionTarget} from './model';
import {NumberTarget} from '../Number/model';

test('it returns true if it is an asset collection target', () => {
  const assetCollectionTarget: AssetCollectionTarget = {
    code: 'response_time',
    type: 'attribute',
    attribute_type: 'pim_catalog_asset_collection',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isAssetCollectionTarget(assetCollectionTarget)).toEqual(true);
});

test('it returns false if it is not an asset collection target', () => {
  const numberTarget: NumberTarget = {
    code: 'pieces_count',
    type: 'attribute',
    attribute_type: 'pim_catalog_number',
    locale: null,
    channel: null,
    source_configuration: {decimal_separator: ','},
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
  };

  expect(isAssetCollectionTarget(numberTarget)).toEqual(false);
});
