import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import Identifier, {
  NormalizedIdentifier,
  createIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import File, {NormalizedFile, denormalizeFile} from 'akeneoassetmanager/domain/model/file';

export interface NormalizedAssetFamilyListItem {
  identifier: NormalizedIdentifier;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export default interface AssetFamilyListItem {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getImage: () => File;
  equals: (assetFamilyListItem: AssetFamilyListItem) => boolean;
  normalize: () => NormalizedAssetFamilyListItem;
}
class InvalidArgumentError extends Error {}

class AssetFamilyListItemImplementation implements AssetFamilyListItem {
  private constructor(private identifier: Identifier, private labelCollection: LabelCollection, private image: File) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('AssetFamilyListItem expects an Identifier as identifier argument');
    }

    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('AssetFamilyListItem expects a LabelCollection as labelCollection argument');
    }

    if (!(image instanceof File)) {
      throw new InvalidArgumentError('AssetFamilyListItem expects a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(identifier: Identifier, labelCollection: LabelCollection, image: File): AssetFamilyListItem {
    return new AssetFamilyListItemImplementation(identifier, labelCollection, image);
  }

  public static createEmpty(): AssetFamilyListItem {
    return new AssetFamilyListItemImplementation(
      createIdentifier(''),
      createLabelCollection({}),
      denormalizeFile(null)
    );
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamilyListItem): AssetFamilyListItem {
    const identifier = createIdentifier(normalizedAssetFamily.identifier);
    const labelCollection = createLabelCollection(normalizedAssetFamily.labels);
    const image = denormalizeFile(normalizedAssetFamily.image);

    return AssetFamilyListItemImplementation.create(identifier, labelCollection, image);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getIdentifier().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(assetFamilyListItem: AssetFamilyListItem): boolean {
    return assetFamilyListItem.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedAssetFamilyListItem {
    return {
      identifier: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
    };
  }
}

export const createAssetFamilyListItem = AssetFamilyListItemImplementation.create;
export const createEmptyAssetFamilyListItem = AssetFamilyListItemImplementation.createEmpty;
export const denormalizeAssetFamilyListItem = AssetFamilyListItemImplementation.createFromNormalized;
