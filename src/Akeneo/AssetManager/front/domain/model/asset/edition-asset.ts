import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {AssetFamily, createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import {getValuesForChannelAndLocale} from 'akeneoassetmanager/domain/model/asset/value-collection';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

type ValueCollection = EditionValue[];

type EditionAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  assetFamily: AssetFamily;
  values: ValueCollection;
};

export const createEmptyEditionAsset = (): EditionAsset => ({
  identifier: '',
  code: '',
  labels: {},
  assetFamily: createEmptyAssetFamily(),
  values: [],
});

export const getEditionAssetCompleteness = (
  asset: EditionAsset,
  channel: ChannelReference,
  locale: LocaleReference
): Completeness => {
  // TODO use completeness light model
  const values = getValuesForChannelAndLocale(asset.values, channel, locale);

  return Completeness.createFromValues(values);
};

export const getEditionAssetLabel = (editionAsset: EditionAsset, locale: LocaleCode) =>
  getLabel(editionAsset.labels, locale, editionAsset.code);

export default EditionAsset;
