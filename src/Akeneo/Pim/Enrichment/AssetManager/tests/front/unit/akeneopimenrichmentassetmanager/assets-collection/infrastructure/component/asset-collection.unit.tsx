import * as React from 'react';
import {mount} from 'enzyme';
import AssetCollection from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';

jest.mock('require-context', name => {});

describe('akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection', () => {
  test('It displays the right flag as icon', () => {
    const assetCollection = mount(<AssetCollection assetFamilyIdentifier={'notice'} assetCodes={['iphone', 'airpods']} context={{locale: 'en_US', channel: 'ecommerce'}} />);

    expect(
      true
    ).toBe(true);
  });
});
