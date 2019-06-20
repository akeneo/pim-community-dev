import File, {NormalizedFile} from 'akeneoassetmanager/domain/model/file';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {NormalizedLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import Identifier, {NormalizedAssetIdentifier} from 'akeneoassetmanager/domain/model/asset/identifier';
import ValueCollection from 'akeneoassetmanager/domain/model/asset/value-collection';
import {NormalizedValue, NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import Completeness, {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {NormalizedCode as NormalizedAssetCode} from 'akeneoassetmanager/domain/model/asset/code';

interface CommonNormalizedAsset {
  identifier: NormalizedAssetIdentifier;
  asset_family_identifier: string;
  code: NormalizedAssetCode;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export interface NormalizedAsset extends CommonNormalizedAsset {
  values: NormalizedValue[];
}

export interface NormalizedItemAsset extends CommonNormalizedAsset {
  values: NormalizedValue[];
  completeness: NormalizedCompleteness;
}

export interface NormalizedMinimalAsset extends CommonNormalizedAsset {
  values: NormalizedMinimalValue[];
}

export enum NormalizeFormat {
  Standard,
  Minimal,
}

export default interface Asset {
  getIdentifier: () => Identifier;
  getCode: () => AssetCode;
  getAssetFamilyIdentifier: () => AssetFamilyIdentifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getValueCollection: () => ValueCollection;
  equals: (asset: Asset) => boolean;
  normalize: () => NormalizedAsset;
  normalizeMinimal: () => NormalizedMinimalAsset;
  getCompleteness: (channel: ChannelReference, locale: LocaleReference) => Completeness;
}

class InvalidArgumentError extends Error {}

class AssetImplementation implements Asset {
  private constructor(
    private identifier: Identifier,
    private assetFamilyIdentifier: AssetFamilyIdentifier,
    private code: AssetCode,
    private labelCollection: LabelCollection,
    private image: File,
    private valueCollection: ValueCollection
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Asset expects a AssetIdentifier as identifier argument');
    }
    if (!(assetFamilyIdentifier instanceof AssetFamilyIdentifier)) {
      throw new InvalidArgumentError('Asset expects an AssetFamilyIdentifier as assetFamilyIdentifier argument');
    }
    if (!(code instanceof AssetCode)) {
      throw new InvalidArgumentError('Asset expects a AssetCode as code argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Asset expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('Asset expects a File as image argument');
    }
    if (!(valueCollection instanceof ValueCollection)) {
      throw new InvalidArgumentError('Asset expects a ValueCollection as valueCollection argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCode: AssetCode,
    labelCollection: LabelCollection,
    image: File,
    valueCollection: ValueCollection
  ): Asset {
    return new AssetImplementation(
      identifier,
      assetFamilyIdentifier,
      assetCode,
      labelCollection,
      image,
      valueCollection
    );
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getAssetFamilyIdentifier(): AssetFamilyIdentifier {
    return this.assetFamilyIdentifier;
  }

  public getCode(): AssetCode {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getValueCollection(): ValueCollection {
    return this.valueCollection;
  }

  public equals(asset: Asset): boolean {
    return asset.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedAsset {
    return {
      identifier: this.getIdentifier().normalize(),
      asset_family_identifier: this.getAssetFamilyIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedMinimalAsset {
    return {
      identifier: this.getIdentifier().normalize(),
      asset_family_identifier: this.getAssetFamilyIdentifier().stringValue(),
      code: this.code.stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      values: this.valueCollection.normalizeMinimal(),
    };
  }

  public getCompleteness(channel: ChannelReference, locale: LocaleReference): Completeness {
    const values = this.getValueCollection().getValuesForChannelAndLocale(channel, locale);

    return Completeness.createFromValues(values);
  }
}

export const createAsset = AssetImplementation.create;
