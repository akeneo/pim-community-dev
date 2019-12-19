import {createEmptyFile, File, createFileFromNormalized} from 'akeneoassetmanager/domain/model/file';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode, {denormalizeAssetCode, assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier, {
  denormalizeAssetIdentifier,
  assetidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset/identifier';
import ValueCollection, {
  getValueFilter,
  getValuesForChannelAndLocale,
} from 'akeneoassetmanager/domain/model/asset/value-collection';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Completeness, {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {mediaLinkDataStringValue, isMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';

interface CommonNormalizedAsset {
  identifier: AssetIdentifier;
  asset_family_identifier: string;
  attribute_as_main_media_identifier: string;
  code: AssetCode;
  labels: LabelCollection;
}

export interface NormalizedAsset extends CommonNormalizedAsset {
  image: Value[];
  values: Value[];
}

export interface NormalizedItemAsset extends CommonNormalizedAsset {
  image: [{filePath: string; originalFilename: string}];
  values: Value[];
  completeness: NormalizedCompleteness;
}

export interface NormalizedMinimalAsset extends CommonNormalizedAsset {
  image: Value[];
  values: Value[];
}

export enum NormalizeFormat {
  Standard,
  Minimal,
}

export default interface Asset {
  getIdentifier: () => AssetIdentifier;
  getCode: () => AssetCode;
  getAssetFamilyIdentifier: () => AssetFamilyIdentifier;
  getAttributeAsMainMediaIdentifier: () => AttributeIdentifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => Value[];
  getValueCollection: () => ValueCollection;
  equals: (asset: Asset) => boolean;
  normalize: () => NormalizedAsset;
  normalizeMinimal: () => NormalizedMinimalAsset;
  getCompleteness: (channel: ChannelReference, locale: LocaleReference) => Completeness;
}

export const getAssetImage = (
  values: Value[],
  attributeAsMainMedia: AttributeIdentifier,
  channel: ChannelCode,
  locale: LocaleCode
): File => {
  const imageValue = values.find(getValueFilter(attributeAsMainMedia, channel, locale));
  if (undefined === imageValue || null === imageValue.data) {
    return createEmptyFile();
  }

  if (isMediaLinkData(imageValue.data)) {
    return createFileFromMediaLinkValue(imageValue);
  }

  if (isMediaFileData(imageValue.data)) {
    return imageValue.data;
  }

  throw Error('The value as main image should be either a MediaLink or a MediaFile');
};

const createFileFromMediaLinkValue = (value: Value): File => {
  if (!isMediaLinkData(value.data)) {
    throw new Error('the value should be a MediaLink value');
  }
  if (!isMediaLinkAttribute(value.attribute)) {
    throw new Error('the value should be a MediaLink attribute');
  }
  const prefix = null !== value.attribute.prefix ? value.attribute.prefix : '';
  const suffix = null !== value.attribute.suffix ? value.attribute.suffix : '';
  const filePath = `${prefix}${value.data}${suffix}`;
  const originalFilename = mediaLinkDataStringValue(value.data);

  return createFileFromNormalized({filePath, originalFilename});
};

class AssetImplementation implements Asset {
  private constructor(
    private identifier: AssetIdentifier,
    private assetFamilyIdentifier: AssetFamilyIdentifier,
    private attributeAsMainMediaIdentifier: AttributeIdentifier,
    private code: AssetCode,
    private labelCollection: LabelCollection,
    private image: Value[],
    private valueCollection: ValueCollection
  ) {
    Object.freeze(this);
  }

  public static create(
    identifier: AssetIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    attributeAsMainMediaIdentifier: AttributeIdentifier,
    assetCode: AssetCode,
    labelCollection: LabelCollection,
    image: Value[],
    valueCollection: ValueCollection
  ): Asset {
    return new AssetImplementation(
      denormalizeAssetIdentifier(identifier),
      denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
      denormalizeAttributeIdentifier(attributeAsMainMediaIdentifier),
      denormalizeAssetCode(assetCode),
      denormalizeLabelCollection(labelCollection),
      image,
      valueCollection
    );
  }

  public getIdentifier(): AssetIdentifier {
    return this.identifier;
  }

  public getAssetFamilyIdentifier(): AssetFamilyIdentifier {
    return this.assetFamilyIdentifier;
  }

  public getAttributeAsMainMediaIdentifier(): AttributeIdentifier {
    return this.attributeAsMainMediaIdentifier;
  }

  public getCode(): AssetCode {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getCode());
  }

  public getImage(): Value[] {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getValueCollection(): ValueCollection {
    return this.valueCollection;
  }

  public equals(asset: Asset): boolean {
    return assetidentifiersAreEqual(asset.getIdentifier(), this.identifier);
  }

  public normalize(): NormalizedAsset {
    return {
      identifier: this.getIdentifier(),
      asset_family_identifier: this.getAssetFamilyIdentifier(),
      attribute_as_main_media_identifier: this.getAttributeAsMainMediaIdentifier(),
      code: assetCodeStringValue(this.code),
      labels: this.getLabelCollection(),
      image: this.getImage(),
      values: this.valueCollection,
    };
  }

  public normalizeMinimal(): NormalizedMinimalAsset {
    return {
      identifier: this.getIdentifier().normalize(),
      asset_family_identifier: this.getAssetFamilyIdentifier(),
      attribute_as_main_media_identifier: this.getAttributeAsMainMediaIdentifier(),
      code: assetCodeStringValue(this.code),
      labels: this.getLabelCollection(),
      image: this.getImage(),
      values: this.valueCollection,
    };
  }

  public getCompleteness(channel: ChannelReference, locale: LocaleReference): Completeness {
    const values = getValuesForChannelAndLocale(this.getValueCollection(), channel, locale);

    return Completeness.createFromValues(values);
  }
}

export const createAsset = AssetImplementation.create;
