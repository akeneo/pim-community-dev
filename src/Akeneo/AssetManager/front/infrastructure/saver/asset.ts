import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import Value from 'akeneoassetmanager/domain/model/asset/value';

const routing = require('routing');

import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';

type BackendCreationAsset = {
  asset_family_identifier: AssetFamilyIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  values: Value[];
};
export const normalizeCreationAsset = (asset: CreationAsset): BackendCreationAsset => ({
  asset_family_identifier: asset.assetFamilyIdentifier,
  code: asset.code,
  labels: asset.labels,
  values: asset.values,
});

interface AssetSaver {
  save: (entity: Asset) => Promise<ValidationError[] | null>;
  create: (entity: CreationAsset) => Promise<ValidationError[] | null>;
}

export class AssetSaverImplementation implements AssetSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(asset: Asset): Promise<ValidationError[] | null> {
    const normalizedAsset = asset.normalizeMinimal();

    const result = await postJSON(
      routing.generate('akeneo_asset_manager_asset_edit_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(asset.getAssetFamilyIdentifier()),
        assetCode: assetCodeStringValue(asset.getCode()),
      }),
      {
        //TODO: temporary fix to make the value light model work https://akeneo.atlassian.net/browse/AST-183
        ...normalizedAsset,
        values: normalizedAsset.values.map((value: Value) => ({...value, attribute: value.attribute.identifier})),
      }
    ).catch(handleError);

    return undefined === result ? null : result;
  }

  async create(asset: CreationAsset): Promise<ValidationError[] | null> {
    const normalizedAsset = normalizeCreationAsset(asset);

    const result = await postJSON(
      routing.generate('akeneo_asset_manager_asset_create_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(asset.assetFamilyIdentifier),
      }),
      normalizedAsset
    ).catch(handleError);

    return undefined === result ? null : result;
  }
}

export default new AssetSaverImplementation();
