import {postJSON} from 'akeneoassetmanager/tools/fetch';
import Attribute, {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import hydrate from 'akeneoassetmanager/application/hydrator/product/attribute';
const routing = require('routing');
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import ProductAttributeFetcher from 'akeneoassetmanager/domain/fetcher/product/attribute';

const ASSET_COLLECTION_ATTRIBUTE_LIMIT = 100;

export class ProductAttributeFetcherImplementation implements ProductAttributeFetcher {
  async fetchLinkedAssetAttributes(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<Attribute[]> {
    const attributes = await postJSON(
      routing.generate('pim_enrich_attribute_rest_index', {
        types: ['pim_catalog_asset_collection'],
        options: {limit: ASSET_COLLECTION_ATTRIBUTE_LIMIT},
      }),
      {}
    );

    return attributes
      .filter((attribute: NormalizedAttribute) =>
        assetFamilyidentifiersAreEqual(
          assetFamilyIdentifier,
          denormalizeAssetFamilyIdentifier(attribute.reference_data_name)
        )
      )
      .map(hydrate);
  }
}

export default new ProductAttributeFetcherImplementation();
