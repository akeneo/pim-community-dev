import {createEmptyFile, File} from 'akeneoassetmanager/domain/model/file';
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
import ValueCollection, {getValueFilter} from 'akeneoassetmanager/domain/model/asset/value-collection';
import Value, {NormalizedValue, NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Completeness, {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {MEDIA_LINK_ATTRIBUTE_TYPE, MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {createFile} from 'akeneoreferenceentity/domain/model/file';

interface CommonNormalizedAsset {
  identifier: AssetIdentifier;
  asset_family_identifier: string;
  attribute_as_main_media_identifier: string;
  code: AssetCode;
  labels: LabelCollection;
}

export interface NormalizedAsset extends CommonNormalizedAsset {
  image: NormalizedValue[];
  values: NormalizedValue[];
}

export interface NormalizedItemAsset extends CommonNormalizedAsset {
  image: [{filePath: string; originalFilename: string}];
  values: NormalizedValue[];
  completeness: NormalizedCompleteness;
}

export interface NormalizedMinimalAsset extends CommonNormalizedAsset {
  image: NormalizedValue[];
  values: NormalizedMinimalValue[];
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
  if (undefined === imageValue || '' === imageValue.data.normalize()) {
    return createEmptyFile();
  }

  return MEDIA_LINK_ATTRIBUTE_TYPE === imageValue.attribute.type
    ? createFileFromMediaLinkValue(imageValue)
    : imageValue.data.normalize();
};

const createFileFromMediaLinkValue = (value: Value) => {
  const attribute = value.attribute as MediaLinkAttribute;
  const prefix = null !== attribute.prefix ? attribute.prefix : '';
  const suffix = null !== attribute.suffix ? attribute.suffix : '';
  const filePath = `${prefix}${value.data.normalize()}${suffix}`;
  const originalFilename = value.data.normalize();

  return createFile(filePath, originalFilename);
};

class InvalidArgumentError extends Error {}

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
    if (!(valueCollection instanceof ValueCollection)) {
      throw new InvalidArgumentError('Asset expects a ValueCollection as valueCollection argument');
    }

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
      image: this.getImage().map((value: Value) => value.normalize()),
      values: this.valueCollection.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedMinimalAsset {
    return {
      identifier: this.getIdentifier().normalize(),
      asset_family_identifier: this.getAssetFamilyIdentifier(),
      attribute_as_main_media_identifier: this.getAttributeAsMainMediaIdentifier(),
      code: assetCodeStringValue(this.code),
      labels: this.getLabelCollection(),
      image: this.getImage().map((value: Value) => value.normalize()),
      values: this.valueCollection.normalizeMinimal(),
    };
  }

  public getCompleteness(channel: ChannelReference, locale: LocaleReference): Completeness {
    const values = this.getValueCollection().getValuesForChannelAndLocale(channel, locale);

    return Completeness.createFromValues(values);
  }
}

export const createAsset = AssetImplementation.create;
