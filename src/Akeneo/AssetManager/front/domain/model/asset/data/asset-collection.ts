import ValueData from 'akeneoassetmanager/domain/model/asset/data';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

class InvalidTypeError extends Error {}

export type NormalizedAssetCollectionData = string[] | null;

class AssetCollectionData extends ValueData {
  private constructor(readonly assetCollectionData: AssetCode[]) {
    super();
    Object.freeze(this);

    if (!Array.isArray(assetCollectionData)) {
      throw new InvalidTypeError('AssetCollectionData expects an array of AssetCode as parameter to be created');
    }

    assetCollectionData.forEach((assetCode: AssetCode) => {
      if (!(assetCode instanceof AssetCode)) {
        throw new InvalidTypeError('AssetCollectionData expects an array of AssetCode as parameter to be created');
      }
    });
  }

  public static create(assetCollectionData: AssetCode[]): AssetCollectionData {
    return new AssetCollectionData(assetCollectionData);
  }

  public static createFromNormalized(
    normalizedAssetCollectionData: NormalizedAssetCollectionData
  ): AssetCollectionData {
    return new AssetCollectionData(
      Array.isArray(normalizedAssetCollectionData)
        ? normalizedAssetCollectionData.map((assetCode: string) => AssetCode.create(assetCode))
        : []
    );
  }

  public isEmpty(): boolean {
    return 0 === this.assetCollectionData.length;
  }

  public equals(data: ValueData): boolean {
    return (
      data instanceof AssetCollectionData &&
      this.assetCollectionData.length === data.assetCollectionData.length &&
      !this.assetCollectionData.some((assetCode: AssetCode, index: number) => {
        return !assetCode.equals(data.assetCollectionData[index]);
      })
    );
  }

  public normalize(): NormalizedAssetCollectionData {
    return this.assetCollectionData.map((assetCode: AssetCode) => assetCode.stringValue());
  }
}

export default AssetCollectionData;
export const create = AssetCollectionData.create;
export const denormalize = AssetCollectionData.createFromNormalized;
