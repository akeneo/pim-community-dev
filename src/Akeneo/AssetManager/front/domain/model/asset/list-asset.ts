import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import ListValue from 'akeneoassetmanager/domain/model/asset/list-value';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceStringValue,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';

export type ValueCollection = {[key: string]: ListValue};

export type PreviewCollection = PreviewModel[];

type PreviewModel = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
  data: {
    filePath: string;
    originalFilename: string;
  };
};

type ListAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  image: PreviewCollection;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  values: ValueCollection;
  completeness: NormalizedCompleteness;
};

export default ListAsset;

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  let key = attributeIdentifierStringValue(attributeIdentifier);
  key = !channelReferenceIsEmpty(channel) ? `${key}_${channelReferenceStringValue(channel)}` : key;
  key = !localeReferenceIsEmpty(locale) ? `${key}_${localeReferenceStringValue(locale)}` : key;

  return key;
};

export const generateValueKey = (value: ListValue) => generateKey(value.attribute, value.channel, value.locale);
