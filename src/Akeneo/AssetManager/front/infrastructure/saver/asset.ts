import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';

const routing = require('routing');

import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';

type BackendValue = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

const normalizeEditionValue = (editionValue: EditionValue): BackendValue => ({
  ...editionValue,
  attribute: editionValue.attribute.identifier,
});

type BackendCreationAsset = {
  asset_family_identifier: AssetFamilyIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  values: BackendValue[];
};

export const normalizeCreationAsset = (asset: CreationAsset): BackendCreationAsset => ({
  asset_family_identifier: asset.assetFamilyIdentifier,
  code: asset.code,
  labels: asset.labels,
  values: undefined === asset.values ? [] : asset.values.map(normalizeEditionValue),
});

type BackendEditionAsset = {
  asset_family_identifier: AssetFamilyIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  values: BackendValue[];
  createdAt: string;
  updatedAt: string;
};

export const normalizeEditionAsset = (asset: EditionAsset): BackendEditionAsset => ({
  asset_family_identifier: asset.assetFamily.identifier,
  code: asset.code,
  labels: asset.labels,
  values: asset.values.map(normalizeEditionValue),
  createdAt: asset.createdAt,
  updatedAt: asset.updatedAt,
});

interface AssetSaver {
  save: (entity: EditionAsset) => Promise<ValidationError[] | null>;
  create: (entity: CreationAsset) => Promise<ValidationError[] | null>;
}

export class AssetSaverImplementation implements AssetSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(asset: EditionAsset): Promise<ValidationError[] | null> {
    const normalizedAsset = normalizeEditionAsset(asset);

    const result = await postJSON(
      routing.generate('akeneo_asset_manager_asset_edit_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(asset.assetFamily.identifier),
        assetCode: assetCodeStringValue(asset.code),
      }),
      normalizedAsset
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
