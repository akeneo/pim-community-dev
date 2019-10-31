import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {
  denormalizeAttributeCode,
  attributecodesAreEqual,
} from 'akeneoassetmanager/domain/model/attribute/code';

export interface NormalizedAssetFamilyCreation {
  code: AttributeCode;
  labels: LabelCollection;
}

export default interface AssetFamilyCreation {
  getCode: () => AttributeCode;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  equals: (assetFamilyCreation: AssetFamilyCreation) => boolean;
  normalize: () => NormalizedAssetFamilyCreation;
}

class AssetFamilyCreationImplementation implements AssetFamilyCreation {
  private constructor(private code: AttributeCode, private labelCollection: LabelCollection) {
    Object.freeze(this);
  }

  public static create(code: AttributeCode, labelCollection: LabelCollection): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(code, labelCollection);
  }

  public static createEmpty(): AssetFamilyCreation {
    return new AssetFamilyCreationImplementation(denormalizeAttributeCode(''), denormalizeLabelCollection({}));
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamilyCreation): AssetFamilyCreation {
    const code = denormalizeAttributeCode(normalizedAssetFamily.code);
    const labelCollection = denormalizeLabelCollection(normalizedAssetFamily.labels);

    return AssetFamilyCreationImplementation.create(code, labelCollection);
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    return getLabelInCollection(this.labelCollection, locale, fallbackOnCode, this.getCode());
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(assetFamilyCreation: AssetFamilyCreation): boolean {
    return attributecodesAreEqual(assetFamilyCreation.getCode(), this.code);
  }

  public normalize(): NormalizedAssetFamilyCreation {
    return {
      code: this.getCode(),
      labels: this.getLabelCollection(),
    };
  }
}

export const createEmptyAssetFamilyCreation = AssetFamilyCreationImplementation.createEmpty;
export const denormalizeAssetFamilyCreation = AssetFamilyCreationImplementation.createFromNormalized;
