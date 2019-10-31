import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AssetIdentifier, {
  assetFamilyidentifiersAreEqual,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import File, {NormalizedFile, denormalizeFile} from 'akeneoassetmanager/domain/model/file';

export interface NormalizedAssetFamilyListItem {
  identifier: AssetIdentifier;
  labels: LabelCollection;
  image: NormalizedFile;
}

export default interface AssetFamilyListItem {
  getIdentifier: () => AssetIdentifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getImage: () => File;
  equals: (assetFamilyListItem: AssetFamilyListItem) => boolean;
  normalize: () => NormalizedAssetFamilyListItem;
}
class InvalidArgumentError extends Error {}

class AssetFamilyListItemImplementation implements AssetFamilyListItem {
  private constructor(
    private identifier: AssetIdentifier,
    private labelCollection: LabelCollection,
    private image: File
  ) {
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('AssetFamilyListItem expects a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: AssetIdentifier,
    labelCollection: LabelCollection,
    image: File
  ): AssetFamilyListItem {
    return new AssetFamilyListItemImplementation(identifier, labelCollection, image);
  }

  public static createEmpty(): AssetFamilyListItem {
    return new AssetFamilyListItemImplementation(
      denormalizeAssetFamilyIdentifier(''),
      denormalizeLabelCollection({}),
      denormalizeFile(null)
    );
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamilyListItem): AssetFamilyListItem {
    const identifier = denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier);
    const labelCollection = denormalizeLabelCollection(normalizedAssetFamily.labels);
    const image = denormalizeFile(normalizedAssetFamily.image);

    return AssetFamilyListItemImplementation.create(identifier, labelCollection, image);
  }

  public getIdentifier(): AssetIdentifier {
    return this.identifier;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getIdentifier());
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(assetFamilyListItem: AssetFamilyListItem): boolean {
    return assetFamilyidentifiersAreEqual(assetFamilyListItem.getIdentifier(), this.identifier);
  }

  public normalize(): NormalizedAssetFamilyListItem {
    return {
      identifier: this.getIdentifier(),
      labels: this.getLabelCollection(),
      image: this.getImage().normalize(),
    };
  }
}

export const createAssetFamilyListItem = AssetFamilyListItemImplementation.create;
export const createEmptyAssetFamilyListItem = AssetFamilyListItemImplementation.createEmpty;
export const denormalizeAssetFamilyListItem = AssetFamilyListItemImplementation.createFromNormalized;
