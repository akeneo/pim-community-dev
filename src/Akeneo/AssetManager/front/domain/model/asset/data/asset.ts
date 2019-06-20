import ValueData from 'akeneoassetmanager/domain/model/asset/data';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

class InvalidTypeError extends Error {}

export type NormalizedAssetData = string | null;

class AssetData extends ValueData {
  private constructor(readonly assetData: AssetCode | null) {
    super();
    Object.freeze(this);

    if (null === assetData) {
      return;
    }

    if (!(assetData instanceof AssetCode)) {
      throw new InvalidTypeError('AssetData expects a AssetCode as parameter to be created');
    }
  }

  public static create(assetData: AssetCode): AssetData {
    return new AssetData(assetData);
  }

  public static createFromNormalized(normalizedAssetData: NormalizedAssetData): AssetData {
    return new AssetData(null === normalizedAssetData ? null : AssetCode.create(normalizedAssetData));
  }

  public isEmpty(): boolean {
    return null === this.assetData;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof AssetData &&
      ((this.isEmpty() && data.isEmpty()) ||
        (data.assetData !== null && this.assetData !== null && this.assetData.equals(data.assetData)))
    );
  }

  public normalize(): NormalizedAssetData {
    return null === this.assetData ? null : this.assetData.stringValue();
  }
}

export default AssetData;
export const create = AssetData.create;
export const denormalize = AssetData.createFromNormalized;
