import {NormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';
import AssetFamilyIdentifier, {
  NormalizedIdentifier as NormalizedAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';

export type NormalizedAssetType = NormalizedAssetFamilyIdentifier | null;

export class InvalidArgumentError extends Error {}
export class InvalidCallError extends Error {}

export class AssetType implements NormalizableAdditionalProperty {
  private constructor(readonly assetType?: AssetFamilyIdentifier) {
    if (undefined === assetType) {
      Object.freeze(this);

      return;
    }

    if (!(assetType instanceof AssetFamilyIdentifier)) {
      throw new InvalidArgumentError('AssetType expects a AssetFamilyIdentifier argument');
    }

    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return typeof value === 'string';
  }

  public static createFromNormalized(normalizedAssetType: NormalizedAssetType) {
    return null === normalizedAssetType
      ? new AssetType()
      : new AssetType(AssetFamilyIdentifier.create(normalizedAssetType));
  }

  public normalize(): NormalizedAssetType {
    return undefined === this.assetType ? null : this.assetType.stringValue();
  }

  public static createFromString(assetType: string) {
    return '' === assetType ? AssetType.createFromNormalized(null) : AssetType.createFromNormalized(assetType);
  }

  public stringValue(): string {
    return undefined === this.assetType ? '' : this.assetType.stringValue();
  }

  public equals(assetType: AssetType) {
    return (
      (undefined === this.assetType && undefined === assetType.assetType) ||
      (undefined !== this.assetType && undefined !== assetType.assetType && this.assetType.equals(assetType.assetType))
    );
  }

  public getAssetFamilyIdentifier(): AssetFamilyIdentifier {
    if (undefined === this.assetType) {
      throw new InvalidCallError('The asset family identifier is undefined');
    }

    return this.assetType;
  }
}
