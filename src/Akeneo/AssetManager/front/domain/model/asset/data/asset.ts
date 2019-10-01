import ValueData from 'akeneoassetmanager/domain/model/asset/data';
import AssetCode, {
  denormalizeAssetCode,
  assetcodesAreEqual,
  assetCodeStringValue,
} from 'akeneoassetmanager/domain/model/asset/code';

export type NormalizedAssetData = string | null;

class AssetData extends ValueData {
  private constructor(readonly assetData: AssetCode | null) {
    super();
    Object.freeze(this);

    if (null === assetData) {
      return;
    }
  }

  public static create(assetData: AssetCode): AssetData {
    return new AssetData(denormalizeAssetCode(assetData));
  }

  public static createFromNormalized(normalizedAssetData: NormalizedAssetData): AssetData {
    return new AssetData(null === normalizedAssetData ? null : denormalizeAssetCode(normalizedAssetData));
  }

  public isEmpty(): boolean {
    return null === this.assetData;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof AssetData &&
      ((this.isEmpty() && data.isEmpty()) ||
        (data.assetData !== null && this.assetData !== null && assetcodesAreEqual(this.assetData, data.assetData)))
    );
  }

  public normalize(): NormalizedAssetData {
    return null === this.assetData ? null : assetCodeStringValue(this.assetData);
  }
}

export default AssetData;
export const create = AssetData.create;
export const denormalize = AssetData.createFromNormalized;
