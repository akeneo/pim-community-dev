import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import Code, {NormalizedCode, createCode} from 'akeneoassetmanager/domain/model/code';

export interface NormalizedAssetFamilyCreation {
  code: NormalizedCode;
  labels: NormalizedLabelCollection;
}

export default interface AssetFamilyCreation {
  getCode: () => Code;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  equals: (assetFamilyCreation: AssetFamilyCreation) => boolean;
  normalize: () => NormalizedAssetFamilyCreation;
}
class InvalidArgumentError extends Error {}

class AssetFamilyCreationImplementation implements AssetFamilyCreation {
  private constructor(private code: Code, private labelCollection: LabelCollection) {
    if (!(code instanceof Code)) {
      throw new InvalidArgumentError('AssetFamilyCreation expects a Code as code argument');
    }

    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('AssetFamilyCreation expects a LabelCollection as labelCollection argument');
    }

    Object.freeze(this);
  }

  public static create(code: Code, labelCollection: LabelCollection): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(code, labelCollection);
  }

  public static createEmpty(): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(createCode(''), createLabelCollection({}));
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamilyCreation): AssetFamilyCreation {
    const code = createCode(normalizedAssetFamily.code);
    const labelCollection = createLabelCollection(normalizedAssetFamily.labels);

    return AssetFamilyCreationImplementation.create(code, labelCollection);
  }

  public getCode(): Code {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(assetFamilyCreation: AssetFamilyCreation): boolean {
    return assetFamilyCreation.getCode().equals(this.code);
  }

  public normalize(): NormalizedAssetFamilyCreation {
    return {
      code: this.getCode().stringValue(),
      labels: this.getLabelCollection().normalize(),
    };
  }
}

export const createAssetFamilyCreation = AssetFamilyCreationImplementation.create;
export const createEmptyAssetFamilyCreation = AssetFamilyCreationImplementation.createEmpty;
export const denormalizeAssetFamilyCreation = AssetFamilyCreationImplementation.createFromNormalized;
