import {NormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyIdentifierStringValue,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';

export type NormalizedAssetType = AssetFamilyIdentifier | null;

export class InvalidArgumentError extends Error {}
export class InvalidCallError extends Error {}

export class AssetType implements NormalizableAdditionalProperty {
  private constructor(readonly assetType?: AssetFamilyIdentifier) {
    if (undefined === assetType) {
      Object.freeze(this);

      return;
    }

    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return typeof value === 'string';
  }

  public static createFromNormalized(normalizedAssetType: NormalizedAssetType) {
    return null === normalizedAssetType
      ? new AssetType()
      : new AssetType(denormalizeAssetFamilyIdentifier(normalizedAssetType));
  }

  public normalize(): NormalizedAssetType {
    return undefined === this.assetType ? null : assetFamilyIdentifierStringValue(this.assetType);
  }

  public static createFromString(assetType: string) {
    return '' === assetType ? AssetType.createFromNormalized(null) : AssetType.createFromNormalized(assetType);
  }

  public stringValue(): string {
    return undefined === this.assetType ? '' : assetFamilyIdentifierStringValue(this.assetType);
  }

  public equals(assetType: AssetType) {
    return (
      (undefined === this.assetType && undefined === assetType.assetType) ||
      (undefined !== this.assetType &&
        undefined !== assetType.assetType &&
        assetFamilyidentifiersAreEqual(this.assetType, assetType.assetType))
    );
  }

  public getAssetFamilyIdentifier(): AssetFamilyIdentifier {
    if (undefined === this.assetType) {
      throw new InvalidCallError('The asset family identifier is undefined');
    }

    return this.assetType;
  }
}
