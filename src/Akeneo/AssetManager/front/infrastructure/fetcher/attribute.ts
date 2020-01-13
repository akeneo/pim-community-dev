import AttributeFetcher from 'akeneoassetmanager/domain/fetcher/attribute';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import hydrator from 'akeneoassetmanager/application/hydrator/attribute';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import {getJSON} from 'akeneoassetmanager/tools/fetch';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

const routing = require('routing');

export class AttributeFetcherImplementation implements AttributeFetcher {
  async fetchAll(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<Attribute[]> {
    const backendAttributes = await this.fetchAllNormalized(assetFamilyIdentifier);

    return hydrateAll<Attribute>(hydrator)(backendAttributes);
  }

  async fetchAllNormalized(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<NormalizedAttribute[]> {
    const backendAttributes = await getJSON(
      routing.generate('akeneo_asset_manager_attribute_index_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
      })
    ).catch(errorHandler);

    return backendAttributes;
  }
}

export default new AttributeFetcherImplementation();
