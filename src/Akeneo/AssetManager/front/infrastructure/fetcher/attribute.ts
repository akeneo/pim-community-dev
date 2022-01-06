import AttributeFetcher from 'akeneoassetmanager/domain/fetcher/attribute';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import hydrator from 'akeneoassetmanager/application/hydrator/attribute';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {validateBackendAttribute} from 'akeneoassetmanager/infrastructure/validator/attribute';

const generateAssetFamilyAttributeListUrl = (identifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${identifier}/attribute`;

export class AttributeFetcherImplementation implements AttributeFetcher {
  async fetchAll(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<Attribute[]> {
    const backendAttributes = await this.fetchAllNormalized(assetFamilyIdentifier);

    return hydrateAll<Attribute>(hydrator)(backendAttributes);
  }

  async fetchAllNormalized(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<NormalizedAttribute[]> {
    const response = await fetch(generateAssetFamilyAttributeListUrl(assetFamilyIdentifier)).catch(errorHandler);
    const backendAttributes = await response.json();

    return backendAttributes.map(validateBackendAttribute);
  }
}

export default new AttributeFetcherImplementation();
